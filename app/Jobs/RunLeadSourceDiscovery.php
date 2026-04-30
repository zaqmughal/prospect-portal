<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\DiscoveryConnector;
use App\Enums\DiscoveryCandidateStatus;
use App\Enums\LeadSourceRunStatus;
use App\Models\Account;
use App\Models\DiscoveryCandidate;
use App\Models\LeadSourceRun;
use App\Services\Discovery\BlocklistService;
use App\Services\Discovery\DomainNormaliser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunLeadSourceDiscovery implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 600;

    public function __construct(
        public LeadSourceRun $run
    ) {}

    public function handle(
        DiscoveryConnector $connector,
        DomainNormaliser $domainNormaliser,
        BlocklistService $blocklistService
    ): void {
        $run = $this->run;
        $leadSource = $run->leadSource;

        $run->update([
            'status' => LeadSourceRunStatus::Running,
            'started_at' => $run->started_at ?? now(),
            'provider' => config('discovery.default_provider', 'serpapi'),
            'config_snapshot' => $leadSource->config,
        ]);

        $errors = [];
        $throttledCount = 0;
        $domainsInRun = [];
        $queriesExecuted = 0;

        try {
            $result = $connector->run($leadSource);
            $errors = $result->getErrors();
            $throttledCount = $result->rateLimitHit ? 1 : 0;
            $items = $result->getItems();
            $queriesExecuted = count(array_unique(array_column($items, 'query')));

            $domainsFound = 0;
            $domainsSkipped = 0;
            $domainsBlocked = 0;
            $domainsCreated = 0;

            foreach ($items as $item) {
                $url = $item['url'] ?? '';
                if ($url === '') {
                    continue;
                }

                if (! $this->isValidUrl($url)) {
                    DiscoveryCandidate::create([
                        'lead_source_run_id' => $run->id,
                        'domain' => $domainNormaliser->normalise($url),
                        'url' => $url,
                        'title' => $item['title'] ?? null,
                        'snippet' => $item['snippet'] ?? null,
                        'query' => $item['query'] ?? null,
                        'position' => $item['position'] ?? null,
                        'status' => DiscoveryCandidateStatus::Blocked,
                        'reason' => 'Invalid URL (non-http(s))',
                    ]);
                    $domainsBlocked++;

                    continue;
                }

                $domain = $domainNormaliser->normalise($url);
                if ($domain === '' || ! $this->hasValidTld($domain)) {
                    DiscoveryCandidate::create([
                        'lead_source_run_id' => $run->id,
                        'domain' => $domain ?: $url,
                        'url' => $url,
                        'title' => $item['title'] ?? null,
                        'snippet' => $item['snippet'] ?? null,
                        'query' => $item['query'] ?? null,
                        'position' => $item['position'] ?? null,
                        'status' => DiscoveryCandidateStatus::Blocked,
                        'reason' => 'Invalid or missing TLD',
                    ]);
                    $domainsBlocked++;

                    continue;
                }

                if (isset($domainsInRun[$domain])) {
                    $domainsSkipped++;

                    continue;
                }

                if (Account::withoutGlobalScope('organization')->where('domain', $domain)->where('organization_id', $leadSource->organization_id)->exists()) {
                    if (! isset($domainsInRun[$domain])) {
                        DiscoveryCandidate::create([
                            'lead_source_run_id' => $run->id,
                            'domain' => $domain,
                            'url' => $url,
                            'title' => $item['title'] ?? null,
                            'snippet' => $item['snippet'] ?? null,
                            'query' => $item['query'] ?? null,
                            'position' => $item['position'] ?? null,
                            'status' => DiscoveryCandidateStatus::Duplicate,
                            'reason' => 'Account already exists',
                        ]);
                        $domainsInRun[$domain] = true;
                    }
                    $domainsSkipped++;

                    continue;
                }

                if ($blocklistService->isBlocked($domain)) {
                    if (! isset($domainsInRun[$domain])) {
                        DiscoveryCandidate::create([
                            'lead_source_run_id' => $run->id,
                            'domain' => $domain,
                            'url' => $url,
                            'title' => $item['title'] ?? null,
                            'snippet' => $item['snippet'] ?? null,
                            'query' => $item['query'] ?? null,
                            'position' => $item['position'] ?? null,
                            'status' => DiscoveryCandidateStatus::Blocked,
                            'reason' => 'Blocklisted domain',
                        ]);
                        $domainsInRun[$domain] = true;
                    }
                    $domainsBlocked++;

                    continue;
                }

                DiscoveryCandidate::create([
                    'lead_source_run_id' => $run->id,
                    'domain' => $domain,
                    'url' => $url,
                    'title' => $item['title'] ?? null,
                    'snippet' => $item['snippet'] ?? null,
                    'query' => $item['query'] ?? null,
                    'position' => $item['position'] ?? null,
                    'status' => DiscoveryCandidateStatus::New,
                    'reason' => null,
                ]);
                $domainsInRun[$domain] = true;
                $domainsFound++;
            }

            $run->update([
                'queries_executed' => $queriesExecuted,
                'throttled_count' => $throttledCount,
                'domains_found' => $domainsFound,
                'domains_skipped' => $domainsSkipped,
                'domains_blocked' => $domainsBlocked,
                'domains_created' => 0,
                'query_errors' => $errors,
                'status' => LeadSourceRunStatus::Success,
                'completed_at' => now(),
            ]);

            $leadSource->update([
                'last_run_at' => $run->started_at,
                'last_run_status' => 'success',
                'last_run_domains_found' => $domainsFound,
            ]);

            if (! config('discovery.approve_before_import', false)) {
                $run->candidates()->where('status', DiscoveryCandidateStatus::New)->update(['status' => DiscoveryCandidateStatus::Approved]);
                PromoteDiscoveryCandidates::dispatch($run);
            }

            Log::info('Lead source discovery run completed', [
                'lead_source_id' => $leadSource->id,
                'run_id' => $run->id,
                'domains_found' => $domainsFound,
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => LeadSourceRunStatus::Failed,
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
                'query_errors' => $errors,
            ]);
            $leadSource->update([
                'last_run_at' => $run->started_at,
                'last_run_status' => 'failed',
                'last_run_domains_found' => null,
            ]);
            Log::error('Lead source discovery run failed', [
                'lead_source_id' => $leadSource->id,
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function isValidUrl(string $url): bool
    {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? '';

        return in_array(strtolower($scheme), ['http', 'https'], true);
    }

    private function hasValidTld(string $domain): bool
    {
        return str_contains($domain, '.') && strlen($domain) > 3;
    }
}
