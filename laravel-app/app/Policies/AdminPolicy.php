<?php

namespace App\Policies;

use App\Models\User;

/**
 * AdminPolicy
 * 
 * Policy for general admin access control.
 * All methods require the user to be an admin.
 */
class AdminPolicy
{
    /**
     * Determine if the user can access admin panel.
     */
    public function access(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can manage users.
     */
    public function manageUsers(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can manage API tokens.
     */
    public function manageApiTokens(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view audit logs.
     */
    public function viewAuditLogs(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can manage settings.
     */
    public function manageSettings(User $user): bool
    {
        return $user->isAdmin();
    }
}
