<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateUserCommand extends Command
{
    protected $signature = 'users:create';

    protected $description = 'Create a user with organization and account context';

    public function handle(): int
    {
        if (! $this->databaseSchemaIsReady()) {
            return self::FAILURE;
        }

        $this->info('Create a new user');

        try {
            [$name, $email, $password, $emailVerifiedAt] = $this->collectUserData();
            [$organization, $role] = $this->collectOrganizationData();
            [$accountName, $accountUrl, $accountDomain, $accountMeta] = $this->collectAccountData($organization);

            DB::transaction(function () use (
                $name,
                $email,
                $password,
                $emailVerifiedAt,
                $organization,
                $role,
                $accountName,
                $accountUrl,
                $accountDomain,
                $accountMeta
            ): void {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);

                if ($emailVerifiedAt !== null) {
                    $user->forceFill(['email_verified_at' => $emailVerifiedAt])->save();
                }

                if ($organization->exists) {
                    $organization->users()->syncWithoutDetaching([
                        $user->id => ['role' => $role],
                    ]);
                } else {
                    $organization->owner_id = $user->id;
                    $organization->save();
                    $organization->users()->attach($user->id, ['role' => 'owner']);
                }

                $user->update(['current_organization_id' => $organization->id]);

                Account::create([
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                    'name' => $accountName,
                    'url' => $accountUrl,
                    'domain' => $accountDomain,
                    'sector' => $accountMeta['sector'],
                    'size_band' => $accountMeta['size_band'],
                    'location' => $accountMeta['location'],
                    'notes' => $accountMeta['notes'],
                ]);

                $this->newLine();
                $this->info('User created successfully.');
                $this->line("User: {$user->name} <{$user->email}>");
                $this->line("Organization: {$organization->name} ({$organization->slug})");
                $this->line("Account: {$accountName} ({$accountDomain})");
            });
        } catch (ValidationException $exception) {
            $this->newLine();
            $this->error('Validation failed:');

            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->line("- {$message}");
                }
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function databaseSchemaIsReady(): bool
    {
        $requiredTables = ['users', 'organizations', 'organization_user', 'accounts'];
        $missingTables = array_values(array_filter(
            $requiredTables,
            static fn (string $table): bool => ! Schema::hasTable($table)
        ));

        if ($missingTables === []) {
            return true;
        }

        $this->newLine();
        $this->error('Cannot create a user because required tables are missing.');
        $this->line('Missing tables: '.implode(', ', $missingTables));
        $this->line('Run `php artisan migrate` and try again.');

        return false;
    }

    /**
     * @return array{0:string,1:string,2:string,3:\Illuminate\Support\Carbon|null}
     */
    private function collectUserData(): array
    {
        $name = trim((string) $this->ask('User name'));
        $email = strtolower(trim((string) $this->ask('User email')));
        $password = (string) $this->secret('User password (min 8 chars)');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $emailVerifiedAt = $this->confirm('Mark email as verified now?', true) ? now() : null;

        return [$name, $email, $password, $emailVerifiedAt];
    }

    /**
     * @return array{0:Organization,1:string}
     */
    private function collectOrganizationData(): array
    {
        $this->newLine();
        $this->info('Company / organization details');

        $useExistingOrganization = Organization::query()->exists() && $this->confirm(
            'Attach to an existing company?',
            false
        );

        if ($useExistingOrganization) {
            $organization = $this->selectExistingOrganization();

            $role = (string) $this->choice(
                'Role in this company',
                ['admin', 'member'],
                0
            );

            return [$organization, $role];
        }

        $name = trim((string) $this->ask('Company name'));
        $slugInput = trim((string) $this->ask('Company slug (leave blank to auto-generate)', ''));
        $plan = (string) $this->choice('Company plan', ['free', 'starter', 'pro', 'enterprise'], 0);

        $slug = $this->buildUniqueOrganizationSlug($slugInput !== '' ? $slugInput : $name);

        $validator = Validator::make([
            'name' => $name,
            'slug' => $slug,
            'plan' => $plan,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Organization::class, 'slug')],
            'plan' => ['required', 'string', Rule::in(['free', 'starter', 'pro', 'enterprise'])],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $organization = new Organization([
            'name' => $name,
            'slug' => $slug,
            'plan' => $plan,
        ]);

        return [$organization, 'owner'];
    }

    /**
     * @return array{0:string,1:string,2:string,3:array{sector:?string,size_band:?string,location:?string,notes:?string}}
     */
    private function collectAccountData(Organization $organization): array
    {
        $this->newLine();
        $this->info('Account details');

        $name = trim((string) $this->ask('Account name'));
        $url = trim((string) $this->ask('Account website URL'));
        $domainInput = trim((string) $this->ask('Account domain (leave blank to derive from URL)', ''));
        $domain = $domainInput !== '' ? strtolower($domainInput) : Account::normalizeDomain($url);
        $sector = $this->nullableInput('Account sector (optional)');
        $sizeBand = $this->nullableInput('Account size band (optional)');
        $location = $this->nullableInput('Account location (optional)');
        $notes = $this->nullableInput('Account notes (optional)');

        $validator = Validator::make([
            'name' => $name,
            'url' => $url,
            'domain' => $domain,
            'sector' => $sector,
            'size_band' => $sizeBand,
            'location' => $location,
            'notes' => $notes,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'domain' => $this->accountDomainRules($organization),
            'sector' => ['nullable', 'string', 'max:255'],
            'size_band' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return [
            $name,
            $url,
            $domain,
            [
                'sector' => $sector,
                'size_band' => $sizeBand,
                'location' => $location,
                'notes' => $notes,
            ],
        ];
    }

    private function nullableInput(string $question): ?string
    {
        $value = trim((string) $this->ask($question, ''));

        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, mixed>
     */
    private function accountDomainRules(Organization $organization): array
    {
        $rules = ['required', 'string', 'max:255'];

        if ($organization->exists) {
            $rules[] = Rule::unique('accounts', 'domain')->where(function ($query) use ($organization): void {
                $query->where('organization_id', $organization->id);
            });
        }

        return $rules;
    }

    private function selectExistingOrganization(): Organization
    {
        $organizations = Organization::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $options = $organizations
            ->mapWithKeys(fn (Organization $organization): array => [
                (string) $organization->id => "{$organization->name} ({$organization->slug})",
            ])
            ->all();

        $organizationId = (int) $this->choice(
            'Select company',
            $options,
            array_key_first($options)
        );

        return $organizations->firstWhere('id', $organizationId)
            ?? Organization::query()->findOrFail($organizationId);
    }

    private function buildUniqueOrganizationSlug(string $seed): string
    {
        $base = Str::slug($seed);
        $base = $base !== '' ? $base : 'organization';
        $slug = $base;
        $counter = 2;

        while (Organization::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
