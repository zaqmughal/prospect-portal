<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $organization->isMember($user);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $organization->isAdmin($user);
    }

    public function manageBilling(User $user, Organization $organization): bool
    {
        return $organization->isOwner($user);
    }

    public function inviteMembers(User $user, Organization $organization): bool
    {
        return $organization->isAdmin($user);
    }

    public function removeMember(User $user, Organization $organization): bool
    {
        return $organization->isAdmin($user);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $organization->isOwner($user);
    }
}
