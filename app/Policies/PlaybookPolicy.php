<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Playbook;
use App\Models\User;

class PlaybookPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Playbook $playbook): bool
    {
        return $playbook->organization_id === $user->current_organization_id;
    }

    public function create(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function update(User $user, Playbook $playbook): bool
    {
        return $playbook->organization_id === $user->current_organization_id;
    }

    public function delete(User $user, Playbook $playbook): bool
    {
        return $playbook->organization_id === $user->current_organization_id;
    }
}
