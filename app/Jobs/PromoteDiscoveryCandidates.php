<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\DiscoveryCandidateStatus;
use App\Enums\PipelineStage;
use App\Enums\ResearchStatus;
use App\Models\Account;
use App\Models\LeadSourceRun;
use App\Services\Discovery\UrlCanonicaliser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PromoteDiscoveryCandidates implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(
        public LeadSourceRun $leadSourceRun
    ) {}

    public function handle(UrlCanonicaliser $urlCanonicaliser): void
    {
        $candidates = $this->leadSourceRun->candidates()
            ->where('status', DiscoveryCandidateStatus::Approved->value)
            ->get();

        $domainsCreated = 0;
        $leadSource = $this->leadSourceRun->leadSource;
        $userId = $leadSource->user_id;
        $orgId = $leadSource->organization_id;
        $configSnapshot = $this->leadSourceRun->config_snapshot ?? [];
        $queries = $configSnapshot['queries'] ?? [];

        foreach ($candidates as $candidate) {
            $account = Account::withoutGlobalScope('organization')
                ->where('domain', $candidate->domain)
                ->where('organization_id', $orgId)
                ->first();

            if ($account !== null) {
                if ($urlCanonicaliser->isBetterThan($candidate->url, $account->url)) {
                    $account->update(['url' => $candidate->url]);
                }
                $account->update([
                    'discovery_metadata' => array_merge($account->discovery_metadata ?? [], [
                        'query' => $queries[0] ?? null,
                        'result_position' => null,
                    ]),
                ]);

                continue;
            }

            $name = $candidate->title ?? $candidate->domain;
            $account = Account::create([
                'organization_id' => $orgId,
                'user_id' => $userId,
                'lead_source_id' => $leadSource->id,
                'name' => $name,
                'url' => $candidate->url,
                'domain' => $candidate->domain,
                'pipeline_stage' => PipelineStage::New,
                'research_status' => ResearchStatus::Pending,
                'discovered_at' => now(),
                'discovery_metadata' => [
                    'query' => $queries[0] ?? null,
                    'result_position' => null,
                ],
            ]);
            $domainsCreated++;

            RunAccountResearch::dispatch($account, 'discovery');
        }

        $this->leadSourceRun->update([
            'domains_created' => $domainsCreated,
        ]);

        Log::info('Promotion job completed', [
            'lead_source_run_id' => $this->leadSourceRun->id,
            'domains_created' => $domainsCreated,
        ]);
    }
}
