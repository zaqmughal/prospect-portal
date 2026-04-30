<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\PipelineStage;
use App\Enums\ResearchStatus;
use App\Models\Account;
use App\Models\ResearchRun;
use App\Services\AI\OpenAIService;
use App\Services\OnboardingService;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function dismissOnboarding(): void
    {
        $organization = Auth::user()?->currentOrganization;

        if ($organization) {
            app(OnboardingService::class)->dismiss($organization);
        }
    }

    public function reopenOnboarding(): void
    {
        $organization = Auth::user()?->currentOrganization;

        if ($organization) {
            app(OnboardingService::class)->reset($organization);
        }
    }

    public function render(): View
    {
        $organization = Auth::user()->currentOrganization;
        $orgId = $organization?->id;

        $pipelineCounts = [];
        foreach (PipelineStage::cases() as $stage) {
            $pipelineCounts[$stage->value] = Account::where('pipeline_stage', $stage->value)->count();
        }

        $researchCounts = [];
        foreach (ResearchStatus::cases() as $status) {
            $researchCounts[$status->value] = Account::where('research_status', $status->value)->count();
        }

        $hotLeads = Account::where('lead_score', '>', 0)
            ->orderByDesc('lead_score')
            ->limit(20)
            ->get();

        $recentRuns = ResearchRun::whereHas('account', function ($q) use ($orgId) {
            if ($orgId) {
                $q->where('organization_id', $orgId);
            }
        })
            ->with('account')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        if ($organization) {
            $planLimits = app(PlanLimitService::class);
            $todaySpend = $planLimits->getOrgDailySpend($organization);
            $dailyLimit = $planLimits->getDailyAiSpendLimit($organization);
        } else {
            $todaySpend = app(OpenAIService::class)->getTodaySpend();
            $dailyLimit = (float) config('services.openai.daily_limit', 5.00);
        }

        $totalAccounts = Account::count();
        $researchedToday = ResearchRun::whereHas('account', function ($q) use ($orgId) {
            if ($orgId) {
                $q->where('organization_id', $orgId);
            }
        })
            ->whereDate('created_at', today())
            ->count();

        $onboarding = app(OnboardingService::class);
        $showOnboarding = $organization && $onboarding->shouldShow($organization);
        $onboardingSteps = $organization ? $onboarding->steps($organization) : [];
        $onboardingProgress = $organization ? $onboarding->progress($organization) : 0;

        return view('livewire.dashboard', [
            'pipelineCounts' => $pipelineCounts,
            'researchCounts' => $researchCounts,
            'hotLeads' => $hotLeads,
            'recentRuns' => $recentRuns,
            'todaySpend' => $todaySpend,
            'dailyLimit' => $dailyLimit,
            'totalAccounts' => $totalAccounts,
            'researchedToday' => $researchedToday,
            'showOnboarding' => $showOnboarding,
            'onboardingSteps' => $onboardingSteps,
            'onboardingProgress' => $onboardingProgress,
        ]);
    }
}
