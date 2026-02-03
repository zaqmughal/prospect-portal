<?php

declare(strict_types=1);

namespace App\Livewire\LeadSources;

use App\Enums\LeadSourceCadence;
use App\Enums\LeadSourceStatus;
use App\Enums\LeadSourceType;
use App\Models\LeadSource;
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

    public function addQuery(): void
    {
        $this->queries[] = '';
    }

    public function removeQuery(int $index): void
    {
        unset($this->queries[$index]);
        $this->queries = array_values($this->queries);
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
        ]);
    }
}
