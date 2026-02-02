<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\AuditLog;
use Illuminate\View\View;
use Illuminate\Http\Request;

/**
 * AdminDashboardController
 * 
 * Handles admin dashboard display with metrics and recent activity.
 */
class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $this->authorize('viewDashboard', auth()->user());

        // Calculate metrics
        $metrics = [
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::sum('total'),
            'recent_orders' => Order::with('user')->latest()->take(10)->get(),
            'recent_logs' => AuditLog::with('user')->latest()->take(10)->get(),
        ];

        return view('admin.dashboard', $metrics);
    }
}
