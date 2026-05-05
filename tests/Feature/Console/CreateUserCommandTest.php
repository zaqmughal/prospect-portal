<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Account;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_user_organization_and_account_in_one_flow(): void
    {
        $this->artisan('users:create')
            ->expectsQuestion('User name', 'New User')
            ->expectsQuestion('User email', 'new.user@example.com')
            ->expectsQuestion('User password (min 8 chars)', 'super-secret')
            ->expectsQuestion('Confirm password', 'super-secret')
            ->expectsConfirmation('Mark email as verified now?', 'yes')
            ->expectsQuestion('Company name', 'Acme Limited')
            ->expectsQuestion('Company slug (leave blank to auto-generate)', '')
            ->expectsQuestion('Account name', 'Acme Target Account')
            ->expectsQuestion('Account website URL', 'https://acme.example.com')
            ->expectsQuestion('Account domain (leave blank to derive from URL)', '')
            ->expectsQuestion('Account sector (optional)', '')
            ->expectsQuestion('Account size band (optional)', '')
            ->expectsQuestion('Account location (optional)', '')
            ->expectsQuestion('Account notes (optional)', '')
            ->assertSuccessful();

        $user = User::query()->where('email', 'new.user@example.com')->firstOrFail();
        $organization = Organization::query()->where('name', 'Acme Limited')->firstOrFail();
        $account = Account::query()->where('name', 'Acme Target Account')->firstOrFail();

        $this->assertSame($user->id, $organization->owner_id);
        $this->assertSame($organization->id, $user->current_organization_id);
        $this->assertTrue($organization->users()->where('users.id', $user->id)->exists());
        $this->assertSame(
            'owner',
            $organization->users()->where('users.id', $user->id)->firstOrFail()->pivot->role
        );

        $this->assertSame($organization->id, $account->organization_id);
        $this->assertSame($user->id, $account->user_id);
        $this->assertSame('acme.example.com', $account->domain);
    }

    public function test_it_supports_non_interactive_creation_via_flags(): void
    {
        $this->artisan('users:create', [
            '--name' => 'Cloud User',
            '--email' => 'cloud.user@example.com',
            '--password' => 'super-secret',
            '--password-confirmation' => 'super-secret',
            '--email-verified' => true,
            '--company-name' => 'Cloud Co',
            '--company-slug' => 'cloud-co',
            '--company-plan' => 'starter',
            '--account-name' => 'Cloud Primary Account',
            '--account-url' => 'https://www.cloud-co.example.com',
            '--account-domain' => 'cloud-co.example.com',
            '--account-sector' => 'SaaS',
            '--account-size-band' => '51-200',
            '--account-location' => 'London',
            '--account-notes' => 'Created from cloud command UI',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $user = User::query()->where('email', 'cloud.user@example.com')->firstOrFail();
        $organization = Organization::query()->where('slug', 'cloud-co')->firstOrFail();
        $account = Account::query()->where('domain', 'cloud-co.example.com')->firstOrFail();

        $this->assertSame($user->id, $organization->owner_id);
        $this->assertSame($organization->id, $user->current_organization_id);
        $this->assertSame('starter', $organization->plan);
        $this->assertSame($organization->id, $account->organization_id);
        $this->assertSame($user->id, $account->user_id);
        $this->assertSame('SaaS', $account->sector);
        $this->assertSame('51-200', $account->size_band);
    }
}
