<?php

namespace App\Policies;

use App\Models\Enquiry;
use App\Models\User;

class EnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function view(User $user, Enquiry $enquiry): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function create(User $user): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function update(User $user, Enquiry $enquiry): bool
    {
        return $user->canManageListingsAndEnquiries();
    }

    public function delete(User $user, Enquiry $enquiry): bool
    {
        return $user->isAdministrator();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdministrator();
    }
}
