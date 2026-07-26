<?php

namespace App\Policies;

use App\Models\PropertyImage;
use App\Models\User;

class PropertyImagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function view(User $user, PropertyImage $propertyImage): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function create(User $user): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function update(User $user, PropertyImage $propertyImage): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function delete(User $user, PropertyImage $propertyImage): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }
}
