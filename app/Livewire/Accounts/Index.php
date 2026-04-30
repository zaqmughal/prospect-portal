<?php

declare(strict_types=1);

namespace App\Livewire\Accounts;

use App\Enums\PipelineStage;
use App\Enums\ResearchStatus;
use App\Jobs\RunAccountResearch;
use App\Models\Account;
use App\Models\LeadSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $pipelineStage = '';

    #[Url]
    public string $researchStatus = '';

    #[Url]
    public string $sortBy = 'lead_score';

    #[Url]
    public string $sortDir = 'desc';

    #[Url]
    public string $leadSourceId = '';

    /** 'none' | 'lead_source' */
    #[Url]
    public string $groupBy = '';

    /**
     * @var array<int>
     */
    public array $selected = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLeadSourceId(): void
    {
        $this->resetPage();
    }

    public function updatingGroupBy(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'desc';
        }
    }

    public function runResearch(int $accountId): void
    {
        $account = Account::findOrFail($accountId);
        $account->update(['research_status' => ResearchStatus::Queued]);
        RunAccountResearch::dispatch($account, 'manual');
        $this->dispatch('notify', message: 'Research queued for '.$account->name);
    }

    public function runSelectedResearch(): void
    {
        $accounts = Account::query()
            ->whereIn('id', $this->selected)
            ->get();

        foreach ($accounts as $account) {
            $account->update(['research_status' => ResearchStatus::Queued]);
            RunAccountResearch::dispatch($account, 'bulk');
        }

        $this->selected = [];
        $this->dispatch('notify', message: count($accounts).' accounts queued for research');
    }

    public function delete(int $accountId): void
    {
        Account::where('id', $accountId)->delete();
        $this->dispatch('notify', message: 'Account deleted');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Account::query()
            ->whereIn('id', $this->selected)
            ->delete();

        $this->selected = [];
        $this->dispatch('notify', message: $count === 1
            ? '1 account deleted'
            : "{$count} accounts deleted");
    }

    /**
     * Toggle select-all for the current page. Pass current page IDs from the view.
     *
     * @param  array<int|string>  $pageIds
     */
    public function toggleSelectAllOnPage(array $pageIds): void
    {
        $pageIds = array_map('intval', $pageIds);
        $allSelected = count($pageIds) > 0 && count(array_intersect($this->selected, $pageIds)) === count($pageIds);

        if ($allSelected) {
            $this->selected = array_values(array_diff($this->selected, $pageIds));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $pageIds)));
        }
    }

    public function selectAllInList(): void
    {
        $ids = $this->getAccountsQuery()->limit(500)->pluck('id')->all();
        $this->selected = array_values($ids);
        $this->dispatch('notify', message: count($ids) === 1
            ? '1 account selected'
            : count($ids).' accounts selected');
    }

    private function getAccountsQuery(): Builder
    {
        $query = Account::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('domain', 'like', "%{$this->search}%")
                    ->orWhere('sector', 'like', "%{$this->search}%");
            });
        }

        if ($this->pipelineStage) {
            $query->where('pipeline_stage', $this->pipelineStage);
        }

        if ($this->researchStatus) {
            $query->where('research_status', $this->researchStatus);
        }

        if ($this->leadSourceId !== '') {
            if ($this->leadSourceId === 'none') {
                $query->whereNull('lead_source_id');
            } else {
                $query->where('lead_source_id', (int) $this->leadSourceId);
            }
        }

        if ($this->groupBy === 'lead_source') {
            $query->orderByRaw('lead_source_id IS NULL')->orderBy('lead_source_id');
        }
        $query->orderBy($this->sortBy, $this->sortDir);

        return $query;
    }

    public function render(): View
    {
        $query = $this->getAccountsQuery();
        $accounts = $query->with('leadSource')->paginate(25);
        $pageIds = $accounts->pluck('id')->all();
        $allOnPageSelected = count($pageIds) > 0 && count(array_intersect($this->selected, $pageIds)) === count($pageIds);

        $accountsGrouped = [];
        if ($this->groupBy === 'lead_source' && $accounts->isNotEmpty()) {
            foreach ($accounts as $account) {
                $key = $account->lead_source_id
                    ? (string) $account->leadSource?->name
                    : 'No lead source';
                if (! isset($accountsGrouped[$key])) {
                    $accountsGrouped[$key] = [];
                }
                $accountsGrouped[$key][] = $account;
            }
        }

        $leadSources = LeadSource::orderBy('name')->get();

        $hasAnyAccounts = Account::query()->exists();
        $hasFilters = $this->search !== ''
            || $this->pipelineStage !== ''
            || $this->researchStatus !== ''
            || $this->leadSourceId !== '';

        return view('livewire.accounts.index', [
            'accounts' => $accounts,
            'accountsGrouped' => $accountsGrouped,
            'pageIds' => $pageIds,
            'allOnPageSelected' => $allOnPageSelected,
            'totalAccountsCount' => min(500, $query->count()),
            'pipelineStages' => PipelineStage::cases(),
            'researchStatuses' => ResearchStatus::cases(),
            'leadSources' => $leadSources,
            'hasAnyAccounts' => $hasAnyAccounts,
            'hasFilters' => $hasFilters,
        ]);
    }
}
