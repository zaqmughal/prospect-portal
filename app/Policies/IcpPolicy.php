<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Icp;
use App\Models\User;

class IcpPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, Icp $icp): bool
    {
        return $icp->organization_id === $user->current_organization_id;
    }

    public function create(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function update(User $user, Icp $icp): bool
    {
        return $icp->organization_id === $user->current_organization_id;
    }

    public function delete(User $user, Icp $icp): bool
    {
        return $icp->organization_id === $user->current_organization_id;
    }
}
