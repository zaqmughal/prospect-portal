<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PipelineStage;
use App\Enums\ResearchRunStatus;
use App\Enums\ResearchStatus;
use App\Models\Account;
use App\Models\Icp;
use App\Models\LeadSource;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\Playbook;
use App\Models\ResearchRun;
use App\Models\User;
use App\Services\OnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class OnboardingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_brand_new_organisation_has_no_completed_steps(): void
    {
        [$organization] = $this->createOrgWithUser();

        $service = app(OnboardingService::class);
        $steps = $service->steps($organization);

        $this->assertCount(5, $steps);
        $this->assertSame(0, $service->progress($organization));
        $this->assertFalse($service->isComplete($organization));
        $this->assertSame('icp', $service->nextStep($organization)['key']);

        foreach ($steps as $step) {
            $this->assertFalse($step['done'], "Step {$step['key']} should be incomplete for a new org");
        }
    }

    public function test_creating_an_icp_completes_the_first_step(): void
    {
        [$organization] = $this->createOrgWithUser();

        Icp::create([
            'organization_id' => $organization->id,
            'name' => 'Test ICP',
            'sectors' => ['Education'],
            'signals' => [],
            'scoring_weights' => [],
            'is_default' => true,
        ]);

        $service = app(OnboardingService::class);
        $steps = collect($service->steps($organization))->keyBy('key');

        $this->assertTrue($steps['icp']['done']);
        $this->assertFalse($steps['accounts']['done']);
        $this->assertSame(20, $service->progress($organization));
        $this->assertSame('accounts', $service->nextStep($organization)['key']);
    }

    public function test_either_a_lead_source_or_an_account_completes_the_accounts_step(): void
    {
        [$orgA, $ownerA] = $this->createOrgWithUser();
        [$orgB, $ownerB] = $this->createOrgWithUser();

        LeadSource::create([
            'organization_id' => $orgA->id,
            'user_id' => $ownerA->id,
            'name' => 'Test source',
            'type' => 'search_query_pack',
            'cadence' => 'weekly',
            'status' => 'enabled',
            'config' => [],
        ]);

        Account::create([
            'organization_id' => $orgB->id,
            'user_id' => $ownerB->id,
            'name' => 'Test account',
            'url' => 'https://example.com',
            'domain' => 'example.com',
            'pipeline_stage' => PipelineStage::New->value,
            'research_status' => ResearchStatus::Pending->value,
        ]);

        $service = app(OnboardingService::class);

        $this->assertTrue(
            collect($service->steps($orgA))->firstWhere('key', 'accounts')['done'],
            'Lead source alone should mark the accounts step done'
        );
        $this->assertTrue(
            collect($service->steps($orgB))->firstWhere('key', 'accounts')['done'],
            'Account alone should also mark the accounts step done'
        );
    }

    public function test_only_completed_research_runs_count_for_the_research_step(): void
    {
        [$organization, $owner] = $this->createOrgWithUser();

        $account = Account::create([
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'name' => 'Test account',
            'url' => 'https://example.com',
            'domain' => 'example.com',
            'pipeline_stage' => PipelineStage::New->value,
            'research_status' => ResearchStatus::Pending->value,
        ]);

        ResearchRun::create([
            'account_id' => $account->id,
            'status' => ResearchRunStatus::Failed->value,
            'triggered_by' => 'manual',
            'pages_fetched' => 0,
            'signals_found' => 0,
            'total_cost' => 0,
        ]);

        $service = app(OnboardingService::class);
        $this->assertFalse(
            collect($service->steps($organization))->firstWhere('key', 'research')['done'],
            'A failed run should not satisfy the research step'
        );

        ResearchRun::create([
            'account_id' => $account->id,
            'status' => ResearchRunStatus::Completed->value,
            'triggered_by' => 'manual',
            'pages_fetched' => 3,
            'signals_found' => 1,
            'total_cost' => 0.01,
        ]);

        $this->assertTrue(
            collect($service->steps($organization))->firstWhere('key', 'research')['done']
        );
    }

    public function test_invite_step_is_completed_by_pending_invitation_or_extra_member(): void
    {
        [$invOrg, $invOwner] = $this->createOrgWithUser();
        [$memberOrg, $memberOwner] = $this->createOrgWithUser();

        OrganizationInvitation::create([
            'organization_id' => $invOrg->id,
            'email' => 'colleague@example.com',
            'role' => 'member',
            'token' => Str::random(64),
        ]);

        $secondMember = User::factory()->create();
        $memberOrg->users()->attach($secondMember->id, ['role' => 'member']);

        $service = app(OnboardingService::class);

        $this->assertTrue(
            collect($service->steps($invOrg))->firstWhere('key', 'invite')['done'],
            'Pending invitation should satisfy the invite step'
        );
        $this->assertTrue(
            collect($service->steps($memberOrg))->firstWhere('key', 'invite')['done'],
            'Having more than one user should satisfy the invite step'
        );
    }

    public function test_dismiss_and_reset_persist_via_settings_json(): void
    {
        [$organization] = $this->createOrgWithUser();
        $service = app(OnboardingService::class);

        $this->assertFalse($service->isDismissed($organization));
        $this->assertTrue($service->shouldShow($organization));

        $service->dismiss($organization);
        $organization->refresh();
        $this->assertTrue($service->isDismissed($organization));
        $this->assertFalse($service->shouldShow($organization));

        $service->reset($organization);
        $organization->refresh();
        $this->assertFalse($service->isDismissed($organization));
        $this->assertTrue($service->shouldShow($organization));
    }

    public function test_should_show_returns_false_when_all_steps_complete(): void
    {
        [$organization, $owner] = $this->createOrgWithUser();
        $service = app(OnboardingService::class);

        Icp::create([
            'organization_id' => $organization->id,
            'name' => 'Test ICP',
            'sectors' => ['Education'],
            'signals' => [],
            'scoring_weights' => [],
            'is_default' => true,
        ]);

        $account = Account::create([
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'name' => 'Test account',
            'url' => 'https://example.com',
            'domain' => 'example.com',
            'pipeline_stage' => PipelineStage::New->value,
            'research_status' => ResearchStatus::Completed->value,
        ]);

        ResearchRun::create([
            'account_id' => $account->id,
            'status' => ResearchRunStatus::Completed->value,
            'triggered_by' => 'manual',
            'pages_fetched' => 3,
            'signals_found' => 1,
            'total_cost' => 0.01,
        ]);

        Playbook::create([
            'organization_id' => $organization->id,
            'name' => 'Test playbook',
            'angle' => 'Test angle',
            'dm_template' => 'Hi',
            'email_template' => 'Subject: Hi',
            'constraints' => [],
            'is_active' => true,
        ]);

        OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'email' => 'colleague@example.com',
            'role' => 'member',
            'token' => Str::random(64),
        ]);

        $this->assertSame(100, $service->progress($organization));
        $this->assertTrue($service->isComplete($organization));
        $this->assertFalse(
            $service->shouldShow($organization),
            'Widget should hide once every step is done'
        );
    }

    /**
     * Create an organisation owned by a freshly factoried user, attach the
     * user as the owner, and authenticate as them so the BelongsToOrganization
     * trait's auto-scope works in tests.
     *
     * @return array{0: Organization, 1: User}
     */
    private function createOrgWithUser(): array
    {
        $user = User::factory()->create();

        $organization = Organization::create([
            'name' => 'Acme '.uniqid(),
            'slug' => 'acme-'.uniqid(),
            'owner_id' => $user->id,
            'plan' => 'free',
        ]);

        $organization->users()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_organization_id' => $organization->id]);

        Auth::login($user->fresh());

        return [$organization->fresh(), $user->fresh()];
    }
}
