<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can update the order status.
     * Defense in depth: even though middleware protects routes, policy provides additional authorization layer.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can cancel the order.
     * Defense in depth: even though middleware protects routes, policy provides additional authorization layer.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
