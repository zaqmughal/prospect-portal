<?php

declare(strict_types=1);

namespace App\Livewire\LeadSources;

use App\Enums\LeadSourceRunStatus;
use App\Enums\LeadSourceRunTrigger;
use App\Jobs\RunLeadSourceDiscovery;
use App\Models\LeadSource;
use App\Models\LeadSourceRun;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * @var array<int>
     */
    public array $selected = [];

    public function runNow(int $leadSourceId): void
    {
        $source = LeadSource::query()->findOrFail($leadSourceId);
        $run = LeadSourceRun::create([
            'lead_source_id' => $source->id,
            'trigger' => LeadSourceRunTrigger::Manual,
            'status' => LeadSourceRunStatus::Queued,
            'started_at' => now(),
        ]);
        RunLeadSourceDiscovery::dispatch($run);
        $this->dispatch('notify', message: 'Discovery run queued for '.$source->name);
    }

    public function delete(int $leadSourceId): void
    {
        LeadSource::query()->findOrFail($leadSourceId)->delete();
        $this->dispatch('notify', message: 'Lead source deleted');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = LeadSource::query()
            ->whereIn('id', $this->selected)
            ->delete();

        $this->selected = [];
        $this->dispatch('notify', message: $count === 1
            ? '1 lead source deleted'
            : "{$count} lead sources deleted");
    }

    /**
     * Toggle select-all for the list. Pass all lead source IDs from the view.
     *
     * @param  array<int|string>  $ids
     */
    public function toggleSelectAllLeadSources(array $ids): void
    {
        $ids = array_map('intval', $ids);
        $allSelected = count($ids) > 0 && count(array_intersect($this->selected, $ids)) === count($ids);

        if ($allSelected) {
            $this->selected = [];
        } else {
            $this->selected = array_values($ids);
        }
    }

    public function render(): View
    {
        $leadSources = LeadSource::query()
            ->with('latestRun')
            ->orderBy('name')
            ->get();
        $ids = $leadSources->pluck('id')->all();
        $allSelected = count($ids) > 0 && count(array_intersect($this->selected, $ids)) === count($ids);
        $hasActiveRuns = $leadSources->contains(
            fn (LeadSource $s) => $s->latestRun && in_array($s->latestRun->status, [LeadSourceRunStatus::Queued, LeadSourceRunStatus::Running], true)
        );

        return view('livewire.lead-sources.index', [
            'leadSources' => $leadSources,
            'allSelected' => $allSelected,
            'ids' => $ids,
            'hasActiveRuns' => $hasActiveRuns,
        ]);
    }
}
