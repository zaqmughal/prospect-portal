<?php

declare(strict_types=1);

namespace App\Livewire\LeadSourceRuns;

use App\Enums\DiscoveryCandidateStatus;
use App\Jobs\PromoteDiscoveryCandidates;
use App\Models\Icp;
use App\Models\LeadSourceRun;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Candidates extends Component
{
    public LeadSourceRun $run;

    public string $statusFilter = '';

    /** @var array<int> */
    public array $selected = [];

    /** Candidate id for the details modal; null when closed. */
    public ?int $detailsCandidateId = null;

    /** Candidate id currently being scored for ICP fit; null when idle. */
    public ?int $scoringCandidateId = null;

    /** True when bulk scoring selected candidates (disables bulk actions). */
    public bool $scoringBulk = false;

    public function mount(LeadSourceRun $run): void
    {
        $orgId = Auth::user()->current_organization_id;
        if ($run->leadSource->organization_id !== $orgId) {
            abort(403);
        }
        $this->run = $run;
    }

    public function showDetails(int $candidateId): void
    {
        $this->run->candidates()->findOrFail($candidateId);
        $this->detailsCandidateId = $candidateId;
    }

    public function closeDetails(): void
    {
        $this->detailsCandidateId = null;
    }

    public function approve(int $candidateId): void
    {
        $candidate = $this->run->candidates()->findOrFail($candidateId);
        if ($candidate->status === DiscoveryCandidateStatus::New) {
            $candidate->update(['status' => DiscoveryCandidateStatus::Approved]);
            if ($this->detailsCandidateId === $candidateId) {
                $this->detailsCandidateId = null;
            }
            $this->dispatch('notify', message: 'Candidate approved');
        }
    }

    public function reject(int $candidateId): void
    {
        $candidate = $this->run->candidates()->findOrFail($candidateId);
        if ($candidate->status === DiscoveryCandidateStatus::New) {
            $candidate->update(['status' => DiscoveryCandidateStatus::Rejected]);
            if ($this->detailsCandidateId === $candidateId) {
                $this->detailsCandidateId = null;
            }
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

    /**
     * Approve all candidates in this run that have status New and ICP fit "high".
     */
    public function approveAllHighIcpFit(): void
    {
        $count = $this->run->candidates()
            ->where('status', DiscoveryCandidateStatus::New)
            ->where('icp_fit', 'high')
            ->update(['status' => DiscoveryCandidateStatus::Approved]);

        if ($count === 0) {
            $this->dispatch('notify', message: 'No high ICP fit candidates to approve.', type: 'warning');

            return;
        }

        $this->dispatch('notify', message: "{$count} high ICP fit candidate(s) approved.");
    }

    /**
     * Toggle select-all for selectable (new) candidates on the page.
     *
     * @param  array<int|string>  $ids
     */
    public function toggleSelectAllCandidates(array $ids): void
    {
        $ids = array_map('intval', $ids);
        $allSelected = count($ids) > 0 && count(array_intersect($this->selected, $ids)) === count($ids);

        if ($allSelected) {
            $this->selected = [];
        } else {
            $this->selected = array_values($ids);
        }
    }

    /**
     * Score all selected candidates against the default ICP (one AI call per candidate).
     * Stops when daily AI budget is reached and reports how many were scored.
     */
    public function scoreSelectedForIcp(): void
    {
        if ($this->selected === []) {
            $this->dispatch('notify', message: 'No candidates selected.', type: 'warning');

            return;
        }

        $icp = Icp::getDefault();
        if ($icp === null) {
            $this->dispatch('notify', message: 'No default ICP configured. Set an ICP as default first.', type: 'warning');

            return;
        }

        $candidates = $this->run->candidates()
            ->whereIn('id', $this->selected)
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty()) {
            $this->selected = [];
            $this->dispatch('notify', message: 'No matching candidates found.', type: 'warning');

            return;
        }

        $this->scoringBulk = true;
        $icpSummary = $this->buildIcpSummary($icp);
        $openai = app(OpenAIService::class);
        $scored = 0;
        $failed = 0;

        foreach ($candidates as $candidate) {
            if (! $openai->isWithinBudget()) {
                $this->dispatch('notify', message: "Daily AI limit reached. Scored {$scored} of ".$candidates->count().' candidates.', type: 'warning');
                $this->scoringBulk = false;

                return;
            }

            $result = $openai->runStandalone('candidate_icp_fit', [
                'title' => $candidate->title ?? '',
                'snippet' => $candidate->snippet ?? '',
                'icp_summary' => $icpSummary,
            ]);

            if ($result['success'] && $result['data'] !== null) {
                $candidate->update([
                    'icp_fit' => $result['data']['fit'],
                    'icp_fit_reason' => $result['data']['reason'],
                ]);
                $scored++;
            } else {
                $failed++;
            }
        }

        $this->scoringBulk = false;
        $this->selected = [];

        $message = $failed > 0
            ? "Scored {$scored} candidates; {$failed} failed."
            : "Scored {$scored} candidates.";
        $this->dispatch('notify', message: $message);
    }

    public function promoteApproved(): void
    {
        $count = $this->run->candidates()->where('status', DiscoveryCandidateStatus::Approved->value)->count();
        if ($count === 0) {
            $this->dispatch('notify', message: 'No approved candidates to promote', type: 'warning');

            return;
        }
        PromoteDiscoveryCandidates::dispatch($this->run);
        $this->dispatch('notify', message: 'Promotion job queued for '.$count.' candidates');
    }

    /**
     * Score a candidate against the default ICP using AI and store fit + reason.
     */
    public function scoreCandidateForIcp(int $candidateId): void
    {
        $candidate = $this->run->candidates()->findOrFail($candidateId);
        $icp = Icp::getDefault();
        if ($icp === null) {
            $this->dispatch('notify', message: 'No default ICP configured. Set an ICP as default first.', type: 'warning');

            return;
        }

        $icpSummary = $this->buildIcpSummary($icp);
        $title = $candidate->title ?? '';
        $snippet = $candidate->snippet ?? '';

        $this->scoringCandidateId = $candidateId;

        $result = app(OpenAIService::class)->runStandalone('candidate_icp_fit', [
            'title' => $title,
            'snippet' => $snippet,
            'icp_summary' => $icpSummary,
        ]);

        $this->scoringCandidateId = null;

        if ($result['success'] && $result['data'] !== null) {
            $candidate->update([
                'icp_fit' => $result['data']['fit'],
                'icp_fit_reason' => $result['data']['reason'],
            ]);
            $this->dispatch('notify', message: 'ICP fit score saved.');
        } else {
            $this->dispatch('notify', message: $result['error'] ?? 'Scoring failed.', type: 'error');
        }
    }

    /**
     * Build a short text summary of the ICP (sectors) for the prompt.
     */
    private function buildIcpSummary(Icp $icp): string
    {
        $parts = [];
        $sectors = $icp->sectors ?? [];
        if (is_array($sectors) && $sectors !== []) {
            $parts[] = 'Sectors: '.implode(', ', array_map('strval', $sectors));
        }
        if ($parts === []) {
            return 'No sectors specified.';
        }

        return implode('. ', $parts);
    }

    public function render(): View
    {
        $query = $this->run->candidates()->orderBy('domain');

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $candidates = $query->get();
        $detailsCandidate = $this->detailsCandidateId !== null
            ? $this->run->candidates()->find($this->detailsCandidateId)
            : null;

        $selectableIds = $candidates
            ->filter(fn ($c) => $c->status === DiscoveryCandidateStatus::New)
            ->pluck('id')
            ->all();
        $allSelected = count($selectableIds) > 0
            && count(array_intersect($this->selected, $selectableIds)) === count($selectableIds);

        $highIcpFitNewCount = (int) $this->run->candidates()
            ->where('status', DiscoveryCandidateStatus::New)
            ->where('icp_fit', 'high')
            ->count();

        return view('livewire.lead-source-runs.candidates', [
            'candidates' => $candidates,
            'statuses' => DiscoveryCandidateStatus::cases(),
            'detailsCandidate' => $detailsCandidate,
            'selectableIds' => $selectableIds,
            'allSelected' => $allSelected,
            'highIcpFitNewCount' => $highIcpFitNewCount,
        ]);
    }
}
