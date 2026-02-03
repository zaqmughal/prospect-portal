<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AiRun;
use App\Models\ResearchRun;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

class Settings extends Component
{
    public function render(): View
    {
        $ai = app(OpenAIService::class);

        $todaySpend = $ai->getTodaySpend();
        $dailyLimit = (float) config('services.openai.daily_limit', 5.00);
        $remainingBudget = $ai->getRemainingBudget();

        $researchLimit = (int) config('services.research.daily_limit', 50);
        $researchToday = (int) Cache::get('research_count:'.date('Y-m-d'), 0);

        // This week's stats
        $weekStart = now()->startOfWeek();
        $weeklyRuns = ResearchRun::whereHas('account', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('created_at', '>=', $weekStart)
            ->count();

        $weeklyCost = ResearchRun::whereHas('account', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('created_at', '>=', $weekStart)
            ->sum('total_cost');

        $totalAiRuns = AiRun::whereHas('account', fn ($q) => $q->where('user_id', Auth::id()))->count();

        return view('livewire.settings', [
            'todaySpend' => $todaySpend,
            'dailyLimit' => $dailyLimit,
            'remainingBudget' => $remainingBudget,
            'researchLimit' => $researchLimit,
            'researchToday' => $researchToday,
            'weeklyRuns' => $weeklyRuns,
            'weeklyCost' => $weeklyCost,
            'totalAiRuns' => $totalAiRuns,
            'apiKeyConfigured' => ! empty(config('services.openai.api_key')),
        ]);
    }
}
