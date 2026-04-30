<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AiRun;
use App\Models\ResearchRun;
use App\Services\AI\OpenAIService;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Settings extends Component
{
    public function render(): View
    {
        $organization = Auth::user()->currentOrganization;
        $planLimits = app(PlanLimitService::class);

        $orgId = $organization?->id;

        if ($organization) {
            $todaySpend = $planLimits->getOrgDailySpend($organization);
            $dailyLimit = $planLimits->getDailyAiSpendLimit($organization);
            $remainingBudget = max(0, $dailyLimit - $todaySpend);
        } else {
            $ai = app(OpenAIService::class);
            $todaySpend = $ai->getTodaySpend();
            $dailyLimit = (float) config('services.openai.daily_limit', 5.00);
            $remainingBudget = $ai->getRemainingBudget();
        }

        $researchToday = ResearchRun::whereHas('account', function ($q) use ($orgId) {
            if ($orgId) {
                $q->where('organization_id', $orgId);
            }
        })->whereDate('created_at', today())->count();

        $weekStart = now()->startOfWeek();
        $weeklyRuns = ResearchRun::whereHas('account', function ($q) use ($orgId) {
            if ($orgId) {
                $q->where('organization_id', $orgId);
            }
        })->where('created_at', '>=', $weekStart)->count();

        $weeklyCost = ResearchRun::whereHas('account', function ($q) use ($orgId) {
            if ($orgId) {
                $q->where('organization_id', $orgId);
            }
        })->where('created_at', '>=', $weekStart)->sum('total_cost');

        $totalAiRuns = AiRun::whereHas('account', function ($q) use ($orgId) {
            if ($orgId) {
                $q->where('organization_id', $orgId);
            }
        })->count();

        return view('livewire.settings', [
            'todaySpend' => $todaySpend,
            'dailyLimit' => $dailyLimit,
            'remainingBudget' => $remainingBudget,
            'researchToday' => $researchToday,
            'weeklyRuns' => $weeklyRuns,
            'weeklyCost' => $weeklyCost,
            'totalAiRuns' => $totalAiRuns,
            'apiKeyConfigured' => ! empty(config('services.openai.api_key')),
            'currentPlan' => $organization?->plan ?? 'free',
            'accountsRemaining' => $organization ? $planLimits->accountsRemaining($organization) : null,
            'researchRunsRemaining' => $organization ? $planLimits->researchRunsRemaining($organization) : null,
        ]);
    }
}
