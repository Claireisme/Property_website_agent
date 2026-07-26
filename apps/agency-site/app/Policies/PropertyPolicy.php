<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function view(User $user, Property $property): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function create(User $user): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function update(User $user, Property $property): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }
}
