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
            ->expectsChoice('Company plan', 'free', ['free', 'starter', 'pro', 'enterprise'])
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
}
