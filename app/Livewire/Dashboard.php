<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\PipelineStage;
use App\Enums\ResearchStatus;
use App\Models\Account;
use App\Models\ResearchRun;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        $userId = Auth::id();

        $pipelineCounts = [];
        foreach (PipelineStage::cases() as $stage) {
            $pipelineCounts[$stage->value] = Account::where('user_id', $userId)
                ->where('pipeline_stage', $stage->value)
                ->count();
        }

        $researchCounts = [];
        foreach (ResearchStatus::cases() as $status) {
            $researchCounts[$status->value] = Account::where('user_id', $userId)
                ->where('research_status', $status->value)
                ->count();
        }

        $hotLeads = Account::where('user_id', $userId)
            ->where('lead_score', '>', 0)
            ->orderByDesc('lead_score')
            ->limit(20)
            ->get();

        $recentRuns = ResearchRun::whereHas('account', fn ($q) => $q->where('user_id', $userId))
            ->with('account')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $todaySpend = app(OpenAIService::class)->getTodaySpend();
        $dailyLimit = (float) config('services.openai.daily_limit', 5.00);

        $totalAccounts = Account::where('user_id', $userId)->count();
        $researchedToday = ResearchRun::whereHas('account', fn ($q) => $q->where('user_id', $userId))
            ->whereDate('created_at', today())
            ->count();

        return view('livewire.dashboard', [
            'pipelineCounts' => $pipelineCounts,
            'researchCounts' => $researchCounts,
            'hotLeads' => $hotLeads,
            'recentRuns' => $recentRuns,
            'todaySpend' => $todaySpend,
            'dailyLimit' => $dailyLimit,
            'totalAccounts' => $totalAccounts,
            'researchedToday' => $researchedToday,
        ]);
    }
}
