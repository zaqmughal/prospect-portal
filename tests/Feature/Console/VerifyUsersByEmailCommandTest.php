<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyUsersByEmailCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_verifies_users_passed_as_email_arguments(): void
    {
        $pendingUser = User::factory()->create([
            'email' => 'pending@example.com',
            'email_verified_at' => null,
        ]);

        $verifiedUser = User::factory()->create([
            'email' => 'already@example.com',
            'email_verified_at' => now()->subDay(),
        ]);

        $this->artisan('users:verify-email', [
            'emails' => ['pending@example.com', 'already@example.com', 'missing@example.com'],
        ])
            ->expectsOutput('Newly verified users: 1')
            ->expectsOutput('Already verified users: 1')
            ->expectsOutput('Missing users: 1')
            ->expectsOutput('Emails not found:')
            ->expectsOutput('- missing@example.com')
            ->assertSuccessful();

        $this->assertNotNull($pendingUser->fresh()->email_verified_at);
        $this->assertTrue(
            $verifiedUser->fresh()->email_verified_at?->equalTo($verifiedUser->email_verified_at) ?? false
        );
    }

    public function test_it_fails_when_any_email_argument_is_invalid(): void
    {
        $this->artisan('users:verify-email', [
            'emails' => ['valid@example.com', 'not-an-email'],
        ])
            ->expectsOutput('Invalid email input.')
            ->assertFailed();
    }
}
