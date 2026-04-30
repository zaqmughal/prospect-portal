<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ResearchRunStatus;
use App\Models\Account;
use App\Models\Icp;
use App\Models\LeadSource;
use App\Models\Organization;
use App\Models\Playbook;
use App\Models\ResearchRun;

/**
 * Computes onboarding checklist state for an organisation.
 *
 * Single source of truth for "what should the user do next?" — both the
 * dashboard widget and any future activation analytics derive their
 * state from here so we never end up with two checklists drifting apart.
 *
 * Step completion is derived from existing data (counts of ICPs, lead
 * sources, accounts, etc.) rather than a separate progress table, so
 * deletes naturally re-open earlier steps and there's no schema to
 * migrate when steps are added or removed.
 */
class OnboardingService
{
    /**
     * Return the ordered onboarding steps with computed completion state.
     *
     * @return list<array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     done: bool,
     *     route: string,
     *     cta: string,
     * }>
     */
    public function steps(Organization $organization): array
    {
        // The BelongsToOrganization trait adds a global scope based on the
        // currently-authenticated user's organisation, so we bypass it here
        // and constrain on $organization->id explicitly. This keeps the
        // service correct when invoked from queues, console commands, or
        // tests where the authenticated user may differ.
        $hasIcp = Icp::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $organization->id)
            ->exists();

        $hasLeadSource = LeadSource::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $organization->id)
            ->exists();

        $hasAccount = Account::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $organization->id)
            ->exists();

        $hasResearch = $this->hasCompletedResearch($organization);

        $hasPlaybook = Playbook::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $organization->id)
            ->exists();

        $hasInvitedTeammate = $organization->invitations()->exists()
            || $organization->users()->count() > 1;

        return [
            [
                'key' => 'icp',
                'label' => 'Define your Ideal Customer Profile',
                'description' => 'Tell the platform who you sell to so we can score and prioritise leads accurately.',
                'done' => $hasIcp,
                'route' => 'icps.create',
                'cta' => 'Create ICP',
            ],
            [
                'key' => 'accounts',
                'label' => 'Add prospect accounts',
                'description' => 'Add a lead source for automated discovery, or create a single account to research now.',
                'done' => $hasLeadSource || $hasAccount,
                'route' => 'lead-sources.create',
                'cta' => 'Add lead source',
            ],
            [
                'key' => 'research',
                'label' => 'Run your first research',
                'description' => 'Generate a research brief, signals, and outreach drafts for one of your accounts.',
                'done' => $hasResearch,
                'route' => 'accounts.index',
                'cta' => 'Open accounts',
            ],
            [
                'key' => 'playbook',
                'label' => 'Create an outreach playbook',
                'description' => 'Define the angle, DM, and email template the AI uses when drafting outreach.',
                'done' => $hasPlaybook,
                'route' => 'playbooks.create',
                'cta' => 'Create playbook',
            ],
            [
                'key' => 'invite',
                'label' => 'Invite a teammate',
                'description' => 'Add colleagues so they can review prospects and contribute to outreach.',
                'done' => $hasInvitedTeammate,
                'route' => 'settings.team',
                'cta' => 'Invite teammate',
            ],
        ];
    }

    /**
     * Percentage 0-100 of completed steps (rounded).
     */
    public function progress(Organization $organization): int
    {
        $steps = $this->steps($organization);
        $total = count($steps);

        if ($total === 0) {
            return 100;
        }

        $done = count(array_filter($steps, fn (array $step): bool => $step['done']));

        return (int) round(($done / $total) * 100);
    }

    public function isComplete(Organization $organization): bool
    {
        foreach ($this->steps($organization) as $step) {
            if (! $step['done']) {
                return false;
            }
        }

        return true;
    }

    public function isDismissed(Organization $organization): bool
    {
        return (bool) ($organization->settings['onboarding']['dismissed'] ?? false);
    }

    /**
     * Whether the dashboard widget should be visible right now.
     *
     * We hide the widget once the user has actively dismissed it OR once
     * every step is complete; users who finish naturally still see the
     * "all done" state once on the next dashboard load before it disappears.
     */
    public function shouldShow(Organization $organization): bool
    {
        return ! $this->isDismissed($organization)
            && ! $this->isComplete($organization);
    }

    public function dismiss(Organization $organization): void
    {
        $this->writeSetting($organization, true);
    }

    public function reset(Organization $organization): void
    {
        $this->writeSetting($organization, false);
    }

    /**
     * Find the first incomplete step, or null if everything is done.
     *
     * @return array{key: string, label: string, description: string, done: bool, route: string, cta: string}|null
     */
    public function nextStep(Organization $organization): ?array
    {
        foreach ($this->steps($organization) as $step) {
            if (! $step['done']) {
                return $step;
            }
        }

        return null;
    }

    private function hasCompletedResearch(Organization $organization): bool
    {
        return ResearchRun::query()
            ->whereHas(
                'account',
                fn ($q) => $q->withoutGlobalScope('organization')
                    ->where('organization_id', $organization->id)
            )
            ->where('status', ResearchRunStatus::Completed->value)
            ->exists();
    }

    private function writeSetting(Organization $organization, bool $dismissed): void
    {
        $settings = $organization->settings ?? [];
        $settings['onboarding'] = array_merge(
            $settings['onboarding'] ?? [],
            ['dismissed' => $dismissed]
        );

        $organization->update(['settings' => $settings]);
    }
}
