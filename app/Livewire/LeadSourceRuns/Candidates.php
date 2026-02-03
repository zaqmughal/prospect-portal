<?php

declare(strict_types=1);

namespace App\Livewire\LeadSourceRuns;

use App\Enums\DiscoveryCandidateStatus;
use App\Jobs\PromoteDiscoveryCandidates;
use App\Models\LeadSourceRun;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Candidates extends Component
{
    public LeadSourceRun $run;

    public string $statusFilter = '';

    /** @var array<int> */
    public array $selected = [];

    public function mount(LeadSourceRun $run): void
    {
        if ($run->leadSource->user_id !== Auth::id()) {
            abort(403);
        }
        $this->run = $run;
    }

    public function approve(int $candidateId): void
    {
        $candidate = $this->run->candidates()->findOrFail($candidateId);
        if ($candidate->status === DiscoveryCandidateStatus::New) {
            $candidate->update(['status' => DiscoveryCandidateStatus::Approved]);
            $this->dispatch('notify', message: 'Candidate approved');
        }
    }

    public function reject(int $candidateId): void
    {
        $candidate = $this->run->candidates()->findOrFail($candidateId);
        if ($candidate->status === DiscoveryCandidateStatus::New) {
            $candidate->update(['status' => DiscoveryCandidateStatus::Rejected]);
            $this->dispatch('notify', message: 'Candidate rejected');
        }
    }

    public function approveSelected(): void
    {
        $this->run->candidates()
            ->whereIn('id', $this->selected)
            ->where('status', DiscoveryCandidateStatus::New)
            ->update(['status' => DiscoveryCandidateStatus::Approved]);
        $this->selected = [];
        $this->dispatch('notify', message: 'Selected candidates approved');
    }

    public function promoteApproved(): void
    {
        $count = $this->run->candidates()->where('status', DiscoveryCandidateStatus::Approved)->count();
        if ($count === 0) {
            $this->dispatch('notify', message: 'No approved candidates to promote', type: 'warning');

            return;
        }
        PromoteDiscoveryCandidates::dispatch($this->run);
        $this->dispatch('notify', message: 'Promotion job queued for '.$count.' candidates');
    }

    public function render(): View
    {
        $query = $this->run->candidates()->orderBy('domain');

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.lead-source-runs.candidates', [
            'candidates' => $query->get(),
            'statuses' => DiscoveryCandidateStatus::cases(),
        ]);
    }
}
