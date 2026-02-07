<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\OutreachChannel;
use App\Enums\ResearchRunStatus;
use App\Enums\ResearchStatus;
use App\Enums\SignalSeverity;
use App\Enums\SignalType;
use App\Enums\SourceType;
use App\Models\Account;
use App\Models\AccountSource;
use App\Models\Brief;
use App\Models\OutreachAsset;
use App\Models\Playbook;
use App\Models\ResearchRun;
use App\Models\SignalEvent;
use App\Services\AI\OpenAIService;
use App\Services\AI\OutputValidator;
use App\Services\Crawler\PoliteCrawler;
use App\Services\Crawler\SnapshotStorage;
use App\Services\ScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RunAccountResearch implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 600;

    public function __construct(
        public Account $account,
        public string $triggeredBy = 'manual'
    ) {}

    public function handle(
        PoliteCrawler $crawler,
        SnapshotStorage $storage,
        OpenAIService $ai,
        OutputValidator $validator,
        ScoringService $scoring
    ): void {
        // Check daily limits
        if (! $this->checkDailyLimits($ai)) {
            return;
        }

        // Create research run
        $run = ResearchRun::create([
            'account_id' => $this->account->id,
            'status' => ResearchRunStatus::Running,
            'triggered_by' => $this->triggeredBy,
            'started_at' => now(),
        ]);

        $this->account->update([
            'research_status' => ResearchStatus::Running,
            'research_blocked_reason' => null,
        ]);

        try {
            // Step 1: Crawl pages
            $sources = $this->crawlPages($crawler, $storage, $run);

            if (empty($sources)) {
                $firstError = $run->sources()->whereNotNull('error_message')->value('error_message');

                throw new \RuntimeException(
                    $firstError
                        ? 'No pages could be fetched. First error: ' . $firstError
                        : 'No pages could be fetched'
                );
            }

            $run->update(['pages_fetched' => count($sources)]);

            // Step 2: Extract company info
            $combinedText = $this->getCombinedText($sources);
            $extractedInfo = $this->runExtractor($ai, $run, $combinedText);

            // Update account with extracted info
            if ($extractedInfo !== null) {
                $this->updateAccountFromExtraction($extractedInfo, $validator);
            }

            // Step 3: Detect signals
            $signals = $this->runSignalDetector($ai, $run, $combinedText, $extractedInfo ?? []);
            $this->saveSignals($run, $signals, $validator);
            $run->update(['signals_found' => count($signals)]);

            // Step 4: Generate brief
            $brief = $this->runBriefGenerator($ai, $run, $extractedInfo ?? [], $signals);

            if ($brief !== null) {
                $this->saveBrief($run, array_merge($brief, ['content_md' => $this->buildFullBriefMarkdown($brief)]));
            }

            // Step 5: Generate outreach
            $this->runOutreachWriter($ai, $run, $brief ?? []);

            // Step 6: Update score
            $scoring->updateScore($this->account);

            // Mark complete
            $run->markAsCompleted();
            $this->account->update([
                'research_status' => ResearchStatus::Completed,
                'last_researched_at' => now(),
            ]);

            Log::info('Research completed', [
                'account_id' => $this->account->id,
                'run_id' => $run->id,
                'pages' => $run->pages_fetched,
                'signals' => $run->signals_found,
                'cost' => $run->total_cost,
            ]);
        } catch (\Throwable $e) {
            $run->markAsFailed($e->getMessage());
            $this->account->update(['research_status' => ResearchStatus::Failed]);

            Log::error('Research failed', [
                'account_id' => $this->account->id,
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function checkDailyLimits(OpenAIService $ai): bool
    {
        // Check AI budget
        if (! $ai->isWithinBudget()) {
            $this->account->update([
                'research_status' => ResearchStatus::QueuedBlocked,
                'research_blocked_reason' => 'Daily budget exceeded',
            ]);
            Log::warning('Research blocked by AI budget', ['account_id' => $this->account->id]);

            return false;
        }

        // Check daily research limit
        $limit = (int) config('services.research.daily_limit', 50);
        $key = 'research_count:'.date('Y-m-d');
        $count = (int) Cache::get($key, 0);

        if ($count >= $limit) {
            $this->account->update([
                'research_status' => ResearchStatus::QueuedBlocked,
                'research_blocked_reason' => 'Daily limit reached',
            ]);
            Log::warning('Research blocked by daily limit', ['account_id' => $this->account->id]);

            return false;
        }

        Cache::put($key, $count + 1, now()->endOfDay());

        return true;
    }

    /**
     * Crawl multiple pages.
     *
     * @return array<AccountSource>
     */
    private function crawlPages(PoliteCrawler $crawler, SnapshotStorage $storage, ResearchRun $run): array
    {
        $baseUrl = rtrim($this->account->url, '/');
        $sources = [];

        $pagesToCrawl = [
            [SourceType::Homepage, ''],
            [SourceType::About, '/about'],
            [SourceType::Services, '/services'],
            [SourceType::Contact, '/contact'],
        ];

        foreach ($pagesToCrawl as [$type, $path]) {
            $url = $baseUrl.$path;
            $result = $crawler->fetch($url);

            $source = AccountSource::create([
                'research_run_id' => $run->id,
                'account_id' => $this->account->id,
                'type' => $type,
                'url' => $url,
                'status' => $result->success ? 'success' : 'failed',
                'error_message' => $result->error,
                'fetched_at' => now(),
                'byte_size' => $result->byteSize,
                'html_hash' => $result->hash,
            ]);

            if ($result->success && $result->html !== null && $result->text !== null) {
                $paths = $storage->store($this->account, $run, $type->value, $result->html, $result->text);
                $source->update([
                    'html_path' => $paths['html_path'],
                    'text_path' => $paths['text_path'],
                ]);
                $sources[] = $source;
            }
        }

        return $sources;
    }

    /**
     * Combine text from all sources.
     *
     * @param  array<AccountSource>  $sources
     */
    private function getCombinedText(array $sources): string
    {
        $texts = [];

        foreach ($sources as $source) {
            $text = $source->getTextContent();
            if ($text !== null) {
                $texts[] = "=== {$source->type->label()} ===\n{$text}";
            }
        }

        return implode("\n\n", $texts);
    }

    /**
     * Run extractor AI.
     *
     * @return array<string, mixed>|null
     */
    private function runExtractor(OpenAIService $ai, ResearchRun $run, string $text): ?array
    {
        $result = $ai->run(
            $this->account,
            $run,
            'extractor',
            ['content' => substr($text, 0, 50000)]
        );

        return $result['success'] ? $result['data'] : null;
    }

    /**
     * Update account from extracted info.
     *
     * @param  array<string, mixed>  $info
     */
    private function updateAccountFromExtraction(array $info, OutputValidator $validator): void
    {
        $sanitised = $validator->sanitiseExtractorOutput($info);

        $updates = [];

        if ($sanitised['sector'] !== null && $this->account->sector === null) {
            $updates['sector'] = $sanitised['sector'];
        }

        if ($sanitised['size_band'] !== null && $this->account->size_band === null) {
            $updates['size_band'] = $sanitised['size_band'];
        }

        if ($sanitised['location'] !== null && $this->account->location === null) {
            $updates['location'] = $sanitised['location'];
        }

        if (! empty($updates)) {
            $this->account->update($updates);
        }
    }

    /**
     * Run signal detector AI.
     *
     * @param  array<string, mixed>  $extractedInfo
     * @return array<array<string, mixed>>
     */
    private function runSignalDetector(OpenAIService $ai, ResearchRun $run, string $text, array $extractedInfo): array
    {
        $result = $ai->run(
            $this->account,
            $run,
            'signal_detector',
            [
                'content' => substr($text, 0, 50000),
                'company_name' => $extractedInfo['company_name'] ?? $this->account->name,
                'sector' => $extractedInfo['sector'] ?? $this->account->sector ?? 'Unknown',
            ]
        );

        return $result['success'] ? ($result['data']['signals'] ?? []) : [];
    }

    /**
     * Save detected signals.
     *
     * @param  array<array<string, mixed>>  $signals
     */
    private function saveSignals(ResearchRun $run, array $signals, OutputValidator $validator): void
    {
        $sanitised = $validator->sanitiseSignalOutput(['signals' => $signals]);

        foreach ($sanitised as $signal) {
            SignalEvent::create([
                'research_run_id' => $run->id,
                'account_id' => $this->account->id,
                'type' => SignalType::from($signal['type']),
                'severity' => SignalSeverity::from($signal['severity']),
                'title' => $signal['title'],
                'description' => $signal['description'],
                'evidence_snippet' => $signal['evidence'],
                'score_impact' => SignalSeverity::from($signal['severity'])->defaultScoreImpact(),
                'detected_at' => now(),
            ]);
        }
    }

    /**
     * Run brief generator AI.
     *
     * @param  array<string, mixed>  $extractedInfo
     * @param  array<array<string, mixed>>  $signals
     * @return array<string, mixed>|null
     */
    private function runBriefGenerator(OpenAIService $ai, ResearchRun $run, array $extractedInfo, array $signals): ?array
    {
        $result = $ai->run(
            $this->account,
            $run,
            'brief_generator',
            [
                'extracted_info' => json_encode($extractedInfo) ?: '{}',
                'signals' => json_encode($signals) ?: '[]',
            ]
        );

        return $result['success'] ? $result['data'] : null;
    }

    /**
     * Build full brief as a single markdown document from all five sections.
     *
     * @param  array<string, mixed>  $briefData
     */
    private function buildFullBriefMarkdown(array $briefData): string
    {
        $sections = [];

        $overview = trim((string) ($briefData['brief_markdown'] ?? ''));
        if ($overview !== '') {
            $sections[] = "## Company Overview\n\n{$overview}";
        }

        $facts = $briefData['facts'] ?? [];
        if (is_array($facts) && $facts !== []) {
            $lines = [];
            foreach ($facts as $fact) {
                $label = $fact['label'] ?? 'Item';
                $value = $fact['value'] ?? '';
                if (is_string($label) && is_string($value)) {
                    $lines[] = "- **{$label}**: {$value}";
                }
            }
            if ($lines !== []) {
                $sections[] = "## Key Facts\n\n".implode("\n", $lines);
            }
        }

        $opportunities = trim((string) ($briefData['opportunities_identified'] ?? ''));
        if ($opportunities !== '') {
            $sections[] = "## Opportunities Identified\n\n{$opportunities}";
        }

        $approach = trim((string) ($briefData['recommended_angle'] ?? ''));
        if ($approach !== '') {
            $sections[] = "## Recommended Approach\n\n{$approach}";
        }

        $talkingPoints = $briefData['talking_points'] ?? [];
        if (is_array($talkingPoints) && $talkingPoints !== []) {
            $lines = [];
            foreach ($talkingPoints as $point) {
                if (is_string($point) && trim($point) !== '') {
                    $lines[] = '- '.trim($point);
                }
            }
            if ($lines !== []) {
                $sections[] = "## Talking Points\n\n".implode("\n", $lines);
            }
        }

        return implode("\n\n", $sections);
    }

    /**
     * Save brief.
     *
     * @param  array<string, mixed>  $briefData
     */
    private function saveBrief(ResearchRun $run, array $briefData): void
    {
        $version = Brief::where('account_id', $this->account->id)->max('version') ?? 0;

        Brief::create([
            'research_run_id' => $run->id,
            'account_id' => $this->account->id,
            'version' => $version + 1,
            'content_md' => $briefData['content_md'] ?? $briefData['brief_markdown'] ?? '',
            'facts' => $briefData['facts'] ?? [],
        ]);
    }

    /**
     * Run outreach writer AI.
     *
     * @param  array<string, mixed>|null  $brief
     */
    private function runOutreachWriter(OpenAIService $ai, ResearchRun $run, ?array $brief): void
    {
        if ($brief === null) {
            return;
        }

        $playbook = Playbook::where('is_active', true)->first();

        if ($playbook === null) {
            return;
        }

        $result = $ai->run(
            $this->account,
            $run,
            'outreach_writer',
            [
                'brief' => json_encode($brief) ?: '{}',
                'playbook_angle' => $playbook->angle,
                'dm_template' => $playbook->dm_template,
                'email_template' => $playbook->email_template,
                'company_description' => config('outreach.company_description'),
                'company_website' => config('outreach.company_website'),
                'linkedin_url' => config('outreach.linkedin_url'),
                'case_study_url' => config('outreach.case_study_url'),
            ]
        );

        if (! $result['success'] || $result['data'] === null) {
            return;
        }

        $data = $result['data'];

        // Save LinkedIn DM
        if (isset($data['linkedin_dm'])) {
            OutreachAsset::create([
                'account_id' => $this->account->id,
                'research_run_id' => $run->id,
                'playbook_id' => $playbook->id,
                'ai_run_id' => $result['ai_run']->id,
                'channel' => OutreachChannel::LinkedIn,
                'content' => $data['linkedin_dm'],
            ]);
        }

        // Save Email (only when body has content so the card is not empty)
        $emailBody = $data['email']['body'] ?? '';
        if (trim((string) $emailBody) !== '') {
            $emailContent = 'Subject: '.($data['email']['subject'] ?? 'No Subject')."\n\n".$emailBody;
            OutreachAsset::create([
                'account_id' => $this->account->id,
                'research_run_id' => $run->id,
                'playbook_id' => $playbook->id,
                'ai_run_id' => $result['ai_run']->id,
                'channel' => OutreachChannel::Email,
                'content' => $emailContent,
            ]);
        }
    }
}
