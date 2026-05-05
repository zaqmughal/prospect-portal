<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VerifyUsersByEmailCommand extends Command
{
    protected $signature = 'users:verify-email
        {emails* : One or more user email addresses to verify}';

    protected $description = 'Mark users as email verified by email address';

    public function handle(): int
    {
        $emails = collect((array) $this->argument('emails'))
            ->map(static fn (mixed $email): string => strtolower(trim((string) $email)))
            ->filter(static fn (string $email): bool => $email !== '')
            ->unique()
            ->values();

        try {
            $this->validateEmails($emails->all());
        } catch (ValidationException $exception) {
            $this->error('Invalid email input.');

            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->line("- {$message}");
                }
            }

            return self::FAILURE;
        }

        $users = User::query()
            ->whereIn('email', $emails->all())
            ->get()
            ->keyBy(static fn (User $user): string => strtolower($user->email));

        $verifiedCount = 0;
        $alreadyVerifiedCount = 0;
        $missingEmails = [];

        foreach ($emails as $email) {
            /** @var User|null $user */
            $user = $users->get($email);

            if (! $user) {
                $missingEmails[] = $email;
                continue;
            }

            if ($user->email_verified_at !== null) {
                $alreadyVerifiedCount++;
                continue;
            }

            $user->forceFill(['email_verified_at' => now()])->save();
            $verifiedCount++;
        }

        $this->info("Newly verified users: {$verifiedCount}");
        $this->line("Already verified users: {$alreadyVerifiedCount}");
        $this->line('Missing users: '.count($missingEmails));

        if ($missingEmails !== []) {
            $this->newLine();
            $this->warn('Emails not found:');
            foreach ($missingEmails as $email) {
                $this->line("- {$email}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $emails
     */
    private function validateEmails(array $emails): void
    {
        Validator::make(
            ['emails' => $emails],
            ['emails.*' => ['required', 'email', 'max:255']]
        )->validate();
    }
}
