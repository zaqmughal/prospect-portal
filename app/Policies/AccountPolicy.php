<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Account $account): bool
    {
        return $account->organization_id === $user->current_organization_id;
    }

    public function create(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function update(User $user, Account $account): bool
    {
        return $account->organization_id === $user->current_organization_id;
    }

    public function delete(User $user, Account $account): bool
    {
        return $account->organization_id === $user->current_organization_id;
    }
}
