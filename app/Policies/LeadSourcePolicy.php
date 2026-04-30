<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LeadSource;
use App\Models\User;

class LeadSourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function view(User $user, LeadSource $leadSource): bool
    {
        return $leadSource->organization_id === $user->current_organization_id;
    }

    public function create(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    public function update(User $user, LeadSource $leadSource): bool
    {
        return $leadSource->organization_id === $user->current_organization_id;
    }

    public function delete(User $user, LeadSource $leadSource): bool
    {
        return $leadSource->organization_id === $user->current_organization_id;
    }
}
