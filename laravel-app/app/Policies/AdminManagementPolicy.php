<?php

namespace App\Policies;

use App\Models\User;

/**
 * AdminManagementPolicy
 * 
 * Policy for admin management operations.
 * All methods require the user to be a Super Admin.
 */
class AdminManagementPolicy
{
    /**
     * Determine if the user can view any admins.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can create an admin invitation.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can invite an admin.
     */
    public function invite(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can deactivate an admin.
     */
    public function deactivate(User $user, User $admin): bool
    {
        // Super Admin can deactivate any admin except themselves
        return $user->isSuperAdmin() && $user->id !== $admin->id;
    }

    /**
     * Determine if the user can activate an admin.
     */
    public function activate(User $user, User $admin): bool
    {
        // Super Admin can activate any admin
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can update an admin's role.
     */
    public function updateRole(User $user, User $admin): bool
    {
        // Super Admin can update any admin's role except their own
        return $user->isSuperAdmin() && $user->id !== $admin->id;
    }

    /**
     * Determine if the user can view an admin.
     */
    public function view(User $user, User $admin): bool
    {
        return $user->isSuperAdmin();
    }
}
