<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Enums\ValidationStatus;
use App\Models\Account;
use App\Models\AiRun;
use App\Models\ResearchRun;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Client;

class OpenAIService
{
    /** System instruction for all AI runs: language and output format. */
    private const SYSTEM_INSTRUCTION = 'You are a helpful assistant. Always respond with valid JSON. Use British English (en-GB) for all text: British spelling (e.g. colour, organisation, optimise, analyse) and UK conventions.';

    private const RUN_TYPE_MODELS = [
        'extractor' => 'fast',
        'signal_detector' => 'fast',
        'brief_generator' => 'quality',
        'outreach_writer' => 'quality',
        'lead_source_generator' => 'fast',
        'candidate_icp_fit' => 'fast',
        'icp_generator' => 'quality',
        'playbook_generator' => 'quality',
    ];

    private const COST_PER_1K_TOKENS = [
        'gpt-4o-mini' => ['input' => 0.00015, 'output' => 0.0006],
        'gpt-4o' => ['input' => 0.0025, 'output' => 0.01],
    ];

    private Client $client;

    private PromptLoader $promptLoader;

    private OutputValidator $validator;

    public function __construct(PromptLoader $promptLoader, OutputValidator $validator)
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('OpenAI API key not configured');
        }

        $this->client = OpenAI::client($apiKey);
        $this->promptLoader = $promptLoader;
        $this->validator = $validator;
    }

    /**
     * Run an AI prompt and return the result.
     *
     * @param  array<string, string>  $variables
     * @return array{success: bool, data: array<string, mixed>|null, error: string|null, ai_run: AiRun}
     */
    public function run(
        Account $account,
        ResearchRun $researchRun,
        string $runType,
        array $variables,
        string $version = 'v1'
    ): array {
        $model = $this->getModelForRunType($runType);
        $prompt = $this->promptLoader->loadWithVariables($runType, $variables, $version);

        $startTime = microtime(true);

        $aiRun = AiRun::create([
            'research_run_id' => $researchRun->id,
            'account_id' => $account->id,
            'run_type' => $runType,
            'model' => $model,
            'prompt_version' => $version,
            'inputs' => $variables,
            'validation_status' => ValidationStatus::Pending,
        ]);

        try {
            $response = $this->client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_INSTRUCTION],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.3,
            ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $content = $response->choices[0]->message->content ?? '';
            $data = json_decode($content, true);

            if (! is_array($data)) {
                $aiRun->update([
                    'outputs' => ['raw' => $content],
                    'duration_ms' => $durationMs,
                    'validation_status' => ValidationStatus::Error,
                    'validation_errors' => 'Failed to parse JSON response',
                    'tokens_input' => $response->usage->promptTokens ?? 0,
                    'tokens_output' => $response->usage->completionTokens ?? 0,
                    'cost_estimate' => $this->calculateCost($model, $response->usage->promptTokens ?? 0, $response->usage->completionTokens ?? 0),
                ]);

                return [
                    'success' => false,
                    'data' => null,
                    'error' => 'Failed to parse JSON response',
                    'ai_run' => $aiRun,
                ];
            }

            $data = $this->validator->normalizeForValidation($runType, $data);

            // Validate against schema
            $validation = $this->validator->validate($runType, $data);

            $aiRun->update([
                'outputs' => $data,
                'duration_ms' => $durationMs,
                'validation_status' => $validation['valid'] ? ValidationStatus::Valid : ValidationStatus::Invalid,
                'validation_errors' => $validation['valid'] ? null : implode('; ', $validation['errors']),
                'tokens_input' => $response->usage->promptTokens ?? 0,
                'tokens_output' => $response->usage->completionTokens ?? 0,
                'cost_estimate' => $this->calculateCost($model, $response->usage->promptTokens ?? 0, $response->usage->completionTokens ?? 0),
            ]);

            $researchRun->addCost((float) $aiRun->cost_estimate);

            $this->trackDailySpend((float) $aiRun->cost_estimate);

            if ($account->organization) {
                app(PlanLimitService::class)->trackOrgDailySpend(
                    $account->organization,
                    (float) $aiRun->cost_estimate
                );
            }

            return [
                'success' => $validation['valid'],
                'data' => $validation['valid'] ? $data : null,
                'error' => $validation['valid'] ? null : implode('; ', $validation['errors']),
                'ai_run' => $aiRun,
            ];
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            Log::error('OpenAI API error', [
                'run_type' => $runType,
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            $aiRun->update([
                'duration_ms' => $durationMs,
                'validation_status' => ValidationStatus::Error,
                'validation_errors' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
                'ai_run' => $aiRun,
            ];
        }
    }

    /**
     * Run an AI prompt without an Account or ResearchRun (standalone).
     * Records the run in ai_runs with research_run_id and account_id null, and tracks daily spend.
     *
     * @param  array<string, string>  $variables
     * @return array{success: bool, data: array<string, mixed>|null, error: string|null, ai_run: AiRun|null}
     */
    public function runStandalone(string $runType, array $variables, string $version = 'v1'): array
    {
        if (! $this->isWithinBudget()) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'Daily AI limit reached. Please try again tomorrow.',
                'ai_run' => null,
            ];
        }

        $model = $this->getModelForRunType($runType);
        $prompt = $this->promptLoader->loadWithVariables($runType, $variables, $version);

        $startTime = microtime(true);

        $aiRun = AiRun::create([
            'research_run_id' => null,
            'account_id' => null,
            'run_type' => $runType,
            'model' => $model,
            'prompt_version' => $version,
            'inputs' => $variables,
            'validation_status' => ValidationStatus::Pending,
        ]);

        try {
            $response = $this->client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_INSTRUCTION],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.3,
            ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $content = $response->choices[0]->message->content ?? '';
            $data = json_decode($content, true);

            if (! is_array($data)) {
                $aiRun->update([
                    'outputs' => ['raw' => $content],
                    'duration_ms' => $durationMs,
                    'validation_status' => ValidationStatus::Error,
                    'validation_errors' => 'Failed to parse JSON response',
                    'tokens_input' => $response->usage->promptTokens ?? 0,
                    'tokens_output' => $response->usage->completionTokens ?? 0,
                    'cost_estimate' => $this->calculateCost($model, $response->usage->promptTokens ?? 0, $response->usage->completionTokens ?? 0),
                ]);
                $this->trackDailySpend((float) $aiRun->fresh()->cost_estimate);

                return [
                    'success' => false,
                    'data' => null,
                    'error' => 'Failed to parse JSON response',
                    'ai_run' => $aiRun,
                ];
            }

            $data = $this->validator->normalizeForValidation($runType, $data);

            $validation = $this->validator->validate($runType, $data);

            $aiRun->update([
                'outputs' => $data,
                'duration_ms' => $durationMs,
                'validation_status' => $validation['valid'] ? ValidationStatus::Valid : ValidationStatus::Invalid,
                'validation_errors' => $validation['valid'] ? null : implode('; ', $validation['errors']),
                'tokens_input' => $response->usage->promptTokens ?? 0,
                'tokens_output' => $response->usage->completionTokens ?? 0,
                'cost_estimate' => $this->calculateCost($model, $response->usage->promptTokens ?? 0, $response->usage->completionTokens ?? 0),
            ]);

            $this->trackDailySpend((float) $aiRun->fresh()->cost_estimate);

            return [
                'success' => $validation['valid'],
                'data' => $validation['valid'] ? $data : null,
                'error' => $validation['valid'] ? null : implode('; ', $validation['errors']),
                'ai_run' => $aiRun,
            ];
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            Log::error('OpenAI API error (standalone)', [
                'run_type' => $runType,
                'error' => $e->getMessage(),
            ]);

            $aiRun->update([
                'duration_ms' => $durationMs,
                'validation_status' => ValidationStatus::Error,
                'validation_errors' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
                'ai_run' => $aiRun,
            ];
        }
    }

    /**
     * Get the model to use for a run type.
     */
    private function getModelForRunType(string $runType): string
    {
        $tier = self::RUN_TYPE_MODELS[$runType] ?? 'fast';

        if ($tier === 'quality') {
            return config('services.openai.model_quality', 'gpt-4o');
        }

        return config('services.openai.model_fast', 'gpt-4o-mini');
    }

    /**
     * Calculate the cost of a run in USD.
     */
    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::COST_PER_1K_TOKENS[$model] ?? self::COST_PER_1K_TOKENS['gpt-4o-mini'];

        $inputCost = ($inputTokens / 1000) * $pricing['input'];
        $outputCost = ($outputTokens / 1000) * $pricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Track daily spend for budget limiting.
     */
    private function trackDailySpend(float $cost): void
    {
        $key = 'ai_daily_spend:'.date('Y-m-d');
        $current = (float) Cache::get($key, 0);
        Cache::put($key, $current + $cost, now()->endOfDay());
    }

    /**
     * Get today's AI spend.
     */
    public function getTodaySpend(): float
    {
        $key = 'ai_daily_spend:'.date('Y-m-d');

        return (float) Cache::get($key, 0);
    }

    /**
     * Check if we're within daily budget.
     */
    public function isWithinBudget(): bool
    {
        $limit = (float) config('services.openai.daily_limit', 5.00);

        return $this->getTodaySpend() < $limit;
    }

    /**
     * Get remaining budget for today.
     */
    public function getRemainingBudget(): float
    {
        $limit = (float) config('services.openai.daily_limit', 5.00);

        return max(0, $limit - $this->getTodaySpend());
    }
}
