<?php

declare(strict_types=1);

namespace App\Livewire\Icps;

use App\Models\Icp;
use App\Services\AI\OpenAIService;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Generate extends Component
{
    public string $businessDescription = '';

    public string $targetMarket = '';

    public bool $generating = false;

    public function generate(): void
    {
        $this->validate([
            'businessDescription' => ['required', 'string', 'max:2000'],
            'targetMarket' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $organization = $user->currentOrganization;

        if (! $organization) {
            $this->addError('businessDescription', 'No organisation selected.');

            return;
        }

        $planLimits = app(PlanLimitService::class);

        if (! $planLimits->canGenerateAiIcp($organization)) {
            $this->addError('businessDescription', 'AI-generated ICPs require a Starter plan or above.');

            return;
        }

        $this->generating = true;

        /** @var OpenAIService $ai */
        $ai = app(OpenAIService::class);
        $result = $ai->runStandalone('icp_generator', [
            'business_description' => trim($this->businessDescription),
            'target_market' => trim($this->targetMarket) ?: 'Not specified',
        ]);

        $this->generating = false;

        if (! $result['success'] || ! is_array($result['data'])) {
            $this->addError('businessDescription', $result['error'] ?? 'AI generation failed. Please try again.');

            return;
        }

        $data = $result['data'];

        Icp::create([
            'organization_id' => $organization->id,
            'name' => is_string($data['name'] ?? null) ? $data['name'] : 'AI-Generated ICP',
            'description' => is_string($data['description'] ?? null) ? $data['description'] : null,
            'sectors' => is_array($data['sectors'] ?? null) ? array_values($data['sectors']) : [],
            'signals' => $this->normaliseSignals($data['signals'] ?? null),
            'scoring_weights' => $this->normaliseScoringWeights($data['scoring_weights'] ?? null),
            'is_default' => false,
        ]);

        session()->flash('message', 'ICP generated and saved successfully.');
        $this->redirect(route('icps.index'), navigate: true);
    }

    /**
     * Coerce AI signals output to {content,ux,tech,opportunity} => {weight,description}.
     * Falls back to neutral weights for any missing categories so ScoringService keeps working.
     *
     * @return array<string, array{weight: int, description: string}>
     */
    private function normaliseSignals(mixed $signals): array
    {
        $categories = ['content', 'ux', 'tech', 'opportunity'];
        $out = [];

        foreach ($categories as $category) {
            $entry = is_array($signals) ? ($signals[$category] ?? null) : null;
            $weight = is_array($entry) && is_numeric($entry['weight'] ?? null)
                ? max(1, min(20, (int) $entry['weight']))
                : 10;
            $description = is_array($entry) && is_string($entry['description'] ?? null)
                ? trim($entry['description'])
                : '';

            $out[$category] = [
                'weight' => $weight,
                'description' => $description,
            ];
        }

        return $out;
    }

    /**
     * Coerce AI scoring weights to {icp_fit,signal_strength,reachability} integers summing to 100.
     *
     * @return array{icp_fit: int, signal_strength: int, reachability: int}
     */
    private function normaliseScoringWeights(mixed $weights): array
    {
        $defaults = ['icp_fit' => 40, 'signal_strength' => 40, 'reachability' => 20];

        if (! is_array($weights)) {
            return $defaults;
        }

        $coerced = [];
        foreach (array_keys($defaults) as $key) {
            $coerced[$key] = is_numeric($weights[$key] ?? null) ? max(0, (int) $weights[$key]) : 0;
        }

        $sum = array_sum($coerced);

        return $sum > 0 ? $coerced : $defaults;
    }

    public function render(): View
    {
        $canGenerate = false;
        $organization = Auth::user()->currentOrganization;

        if ($organization) {
            $canGenerate = app(PlanLimitService::class)->canGenerateAiIcp($organization);
        }

        return view('livewire.icps.generate', [
            'canGenerate' => $canGenerate,
        ]);
    }
}
