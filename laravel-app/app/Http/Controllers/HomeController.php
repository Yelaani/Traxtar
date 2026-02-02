<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the landing page with featured products.
     */
    public function landing(): View
    {
        $products = Product::latest()->take(6)->get();
        
        return view('home', compact('products'));
    }

    /**
     * Redirect to appropriate dashboard based on user role.
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->route('customer.dashboard');
    }

    /**
     * Show admin dashboard.
     */
    public function adminDashboard(): View
    {
        $this->authorize('admin-access');
        
        return view('admin.dashboard', [
            'user' => auth()->user()
        ]);
    }

    /**
     * Show customer dashboard.
     */
    public function customerDashboard(): View
    {
        $this->authorize('customer-access');
        
        $user = auth()->user();
        
        // Calculate cart count
        $cartCount = 0;
        if (session()->has('cart')) {
            foreach (session('cart') as $item) {
                $cartCount += $item['qty'] ?? 0;
            }
        }
        
        // Get recent orders
        $recentOrders = $user->orders()->latest()->take(5)->get();
        
        // Get all orders
        $orders = $user->orders()->latest()->get();
        
        return view('customer.dashboard', [
            'user' => $user,
            'cartCount' => $cartCount,
            'recentOrders' => $recentOrders,
            'orders' => $orders,
        ]);
    }
}
