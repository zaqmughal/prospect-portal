<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\LeadSource;
use App\Models\Organization;
use App\Models\ResearchRun;
use Illuminate\Support\Facades\Cache;

class PlanLimitService
{
    public function canCreateAccount(Organization $organization): bool
    {
        $limit = $this->getLimit($organization, 'accounts_limit');

        if ($limit === null) {
            return true;
        }

        $count = Account::withoutGlobalScope('organization')
            ->where('organization_id', $organization->id)
            ->count();

        return $count < $limit;
    }

    public function canCreateLeadSource(Organization $organization): bool
    {
        $limit = $this->getLimit($organization, 'lead_sources_limit');

        if ($limit === null) {
            return true;
        }

        $count = LeadSource::withoutGlobalScope('organization')
            ->where('organization_id', $organization->id)
            ->count();

        return $count < $limit;
    }

    public function canRunResearch(Organization $organization): bool
    {
        $limit = $this->getLimit($organization, 'research_runs_per_month');

        if ($limit === null) {
            return true;
        }

        $count = ResearchRun::whereHas('account', function ($q) use ($organization) {
            $q->withoutGlobalScope('organization')
                ->where('organization_id', $organization->id);
        })->where('created_at', '>=', now()->startOfMonth())->count();

        return $count < $limit;
    }

    public function canInviteMember(Organization $organization): bool
    {
        $limit = $this->getLimit($organization, 'team_members_limit');

        if ($limit === null) {
            return true;
        }

        return $organization->users()->count() < $limit;
    }

    public function canGenerateAiIcp(Organization $organization): bool
    {
        return (bool) $this->getPlanConfig($organization)['ai_generated_icps'];
    }

    public function canGenerateAiPlaybook(Organization $organization): bool
    {
        return (bool) $this->getPlanConfig($organization)['ai_generated_playbooks'];
    }

    public function getDailyAiSpendLimit(Organization $organization): float
    {
        return (float) ($this->getPlanConfig($organization)['ai_daily_spend_limit'] ?? 1.00);
    }

    public function getOrgDailySpend(Organization $organization): float
    {
        $key = "ai_daily_spend:org:{$organization->id}:".date('Y-m-d');

        return (float) Cache::get($key, 0);
    }

    public function trackOrgDailySpend(Organization $organization, float $cost): void
    {
        $key = "ai_daily_spend:org:{$organization->id}:".date('Y-m-d');
        $current = (float) Cache::get($key, 0);
        Cache::put($key, $current + $cost, now()->endOfDay());
    }

    public function isOrgWithinBudget(Organization $organization): bool
    {
        return $this->getOrgDailySpend($organization) < $this->getDailyAiSpendLimit($organization);
    }

    public function accountsRemaining(Organization $organization): ?int
    {
        $limit = $this->getLimit($organization, 'accounts_limit');

        if ($limit === null) {
            return null;
        }

        $count = Account::withoutGlobalScope('organization')
            ->where('organization_id', $organization->id)
            ->count();

        return max(0, $limit - $count);
    }

    public function researchRunsRemaining(Organization $organization): ?int
    {
        $limit = $this->getLimit($organization, 'research_runs_per_month');

        if ($limit === null) {
            return null;
        }

        $count = ResearchRun::whereHas('account', function ($q) use ($organization) {
            $q->withoutGlobalScope('organization')
                ->where('organization_id', $organization->id);
        })->where('created_at', '>=', now()->startOfMonth())->count();

        return max(0, $limit - $count);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPlanConfig(Organization $organization): array
    {
        return config('plans.'.$organization->plan, config('plans.free'));
    }

    private function getLimit(Organization $organization, string $key): ?int
    {
        $value = $this->getPlanConfig($organization)[$key] ?? null;

        return $value === null ? null : (int) $value;
    }
}
