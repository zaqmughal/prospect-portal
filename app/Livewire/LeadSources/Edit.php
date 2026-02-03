<?php

declare(strict_types=1);

namespace App\Livewire\LeadSources;

use App\Enums\LeadSourceCadence;
use App\Enums\LeadSourceStatus;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Edit extends Component
{
    public LeadSource $leadSource;

    public string $name = '';

    /** @var array<int, string> */
    public array $queries = [];

    public int $perQueryLimit = 10;

    public string $cadence = 'manual';

    public string $status = 'enabled';

    public function mount(LeadSource $leadSource): void
    {
        $this->authorizeLeadSource($leadSource);
        $this->leadSource = $leadSource;
        $this->name = $leadSource->name;
        $config = $leadSource->config ?? [];
        $this->queries = $config['queries'] ?? [''];
        $this->perQueryLimit = (int) ($config['per_query_limit'] ?? 10);
        $this->cadence = $leadSource->cadence->value;
        $this->status = $leadSource->status->value;
    }

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

        $this->leadSource->update([
            'name' => $this->name,
            'config' => array_merge($this->leadSource->config ?? [], [
                'queries' => $queries,
                'per_query_limit' => $this->perQueryLimit,
                'api_key_env' => 'SERPAPI_API_KEY',
            ]),
            'cadence' => LeadSourceCadence::from($this->cadence),
            'status' => LeadSourceStatus::from($this->status),
        ]);

        $this->dispatch('notify', message: 'Lead source updated');

        return $this->redirect(route('lead-sources.index'), navigate: true);
    }

    private function authorizeLeadSource(LeadSource $leadSource): void
    {
        if ($leadSource->user_id !== Auth::id()) {
            abort(403);
        }
    }

    public function render(): View
    {
        return view('livewire.lead-sources.edit', [
            'cadences' => LeadSourceCadence::cases(),
            'statuses' => LeadSourceStatus::cases(),
        ]);
    }
}
