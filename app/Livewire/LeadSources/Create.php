<?php

declare(strict_types=1);

namespace App\Livewire\LeadSources;

use App\Enums\LeadSourceCadence;
use App\Enums\LeadSourceStatus;
use App\Enums\LeadSourceType;
use App\Models\Icp;
use App\Models\LeadSource;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $type = 'search_query_pack';

    /** @var array<int, string> */
    public array $queries = [''];

    public int $perQueryLimit = 10;

    public string $cadence = 'manual';

    public string $status = 'enabled';

    /** Description for AI generation ("Describe the prospects you want to find"). */
    public string $aiDescription = '';

    /** Optional ICP id for AI context; empty string means "None". */
    public string $icpId = '';

    public bool $generating = false;

    public function addQuery(): void
    {
        $this->queries[] = '';
    }

    public function removeQuery(int $index): void
    {
        unset($this->queries[$index]);
        $this->queries = array_values($this->queries);
    }

    public function generateWithAi(): void
    {
        $this->validate([
            'aiDescription' => ['required', 'string', 'max:2000'],
        ], [
            'aiDescription.required' => 'Please describe the prospects you want to find.',
        ]);

        $this->generating = true;
        $this->resetValidation();

        $icpContext = 'No ICP selected';
        if ($this->icpId !== '') {
            $icp = Icp::find($this->icpId);
            if ($icp) {
                $parts = [$icp->name];
                if (! empty($icp->sectors) && is_array($icp->sectors)) {
                    $parts[] = 'Sectors: '.implode(', ', $icp->sectors);
                }
                if (! empty($icp->size_bands) && is_array($icp->size_bands)) {
                    $parts[] = 'Size bands: '.implode(', ', $icp->size_bands);
                }
                $icpContext = implode('. ', $parts);
            }
        }

        /** @var OpenAIService $openAi */
        $openAi = app(OpenAIService::class);
        $result = $openAi->runStandalone('lead_source_generator', [
            'description' => trim($this->aiDescription),
            'icp_context' => $icpContext,
        ]);

        $this->generating = false;

        if (! $result['success']) {
            $this->addError('aiDescription', $result['error'] ?? 'AI generation failed. Please try again.');

            return;
        }

        $data = $result['data'];
        if (! is_array($data) || empty($data['queries']) || ! is_array($data['queries'])) {
            $this->addError('aiDescription', 'AI did not return valid queries. Please try again.');

            return;
        }

        $this->name = isset($data['name']) && is_string($data['name']) ? trim($data['name']) : trim($this->aiDescription);
        $this->queries = array_values(array_filter(array_map('trim', $data['queries'])));
        if (empty($this->queries)) {
            $this->queries = [''];
        }
        $this->dispatch('notify', message: 'Suggestions generated. Edit below and save when ready.');
    }

    public function save(): mixed
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:search_query_pack'],
            'queries' => ['required', 'array', 'min:1'],
            'queries.*' => ['required', 'string', 'max:500'],
            'perQueryLimit' => ['required', 'integer', 'min:1', 'max:50'],
            'cadence' => ['required', 'in:manual,daily,weekly'],
            'status' => ['required', 'in:enabled,disabled'],
        ]);

        $queries = array_values(array_filter(array_map('trim', $this->queries)));
        if (empty($queries)) {
            $this->addError('queries', 'At least one non-empty query is required.');

            return null;
        }

        LeadSource::create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'type' => LeadSourceType::SearchQueryPack,
            'config' => [
                'queries' => $queries,
                'per_query_limit' => $this->perQueryLimit,
                'api_key_env' => 'SERPAPI_API_KEY',
            ],
            'cadence' => LeadSourceCadence::from($this->cadence),
            'status' => LeadSourceStatus::from($this->status),
        ]);

        $this->dispatch('notify', message: 'Lead source created');

        return $this->redirect(route('lead-sources.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lead-sources.create', [
            'cadences' => LeadSourceCadence::cases(),
            'statuses' => LeadSourceStatus::cases(),
            'icps' => Icp::orderBy('name')->get(),
        ]);
    }
}
