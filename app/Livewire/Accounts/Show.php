<?php

declare(strict_types=1);

namespace App\Livewire\Accounts;

use App\Enums\PipelineStage;
use App\Enums\ResearchStatus;
use App\Jobs\RunAccountResearch;
use App\Models\Account;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public int $accountId;

    public Account $account;

    public string $activeTab = 'overview';

    public function mount(int $accountId): void
    {
        $this->accountId = $accountId;
        $this->account = Account::query()
            ->with(['signalEvents', 'researchRuns' => fn ($q) => $q->latest()->limit(5), 'latestBrief', 'outreachAssets'])
            ->findOrFail($accountId);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function runResearch(): void
    {
        $this->account->update(['research_status' => ResearchStatus::Queued]);
        RunAccountResearch::dispatch($this->account, 'manual');
        $this->dispatch('notify', message: 'Research queued');
    }

    public function updatePipelineStage(string $stage): void
    {
        $this->account->update(['pipeline_stage' => PipelineStage::from($stage)]);
        $this->dispatch('notify', message: 'Pipeline stage updated');
    }

    public function render(): View
    {
        $this->account->refresh();
        $this->account->load([
            'signalEvents',
            'researchRuns' => fn ($q) => $q->latest()->limit(5),
            'latestBrief',
            'outreachAssets',
        ]);

        return view('livewire.accounts.show', [
            'pipelineStages' => PipelineStage::cases(),
        ]);
    }
}
