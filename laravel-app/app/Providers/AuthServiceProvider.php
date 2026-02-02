<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => \App\Policies\OrderPolicy::class,
        User::class => \App\Policies\AdminManagementPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     * 
     * Authorization is enforced consistently across both web (session-based) and API (Sanctum token-based) layers.
     * Admin roles are also enforced at API level using Sanctum token abilities, ensuring consistent authorization
     * across all access methods. This provides defense in depth and prevents privilege escalation.
     * 
     * Least Privilege Principle: Admins are granted minimum privileges required for their role.
     * - Regular admins can manage products and orders but cannot manage other admins.
     * - Super admins have additional privileges to manage admin accounts and invitations.
     * - All role assignments follow the principle of least privilege to minimize security risk.
     */
    public function boot(): void
    {
        // Admin access gate (enforced in both web and API via Sanctum)
        Gate::define('admin-access', function ($user) {
            return $user->isAdmin();
        });

        // Customer access gate (enforced in both web and API via Sanctum)
        Gate::define('customer-access', function ($user) {
            return $user->isCustomer();
        });

        // Super Admin access gate (enforced in both web and API via Sanctum)
        Gate::define('super-admin-access', function ($user) {
            return $user->isSuperAdmin();
        });
    }
}
