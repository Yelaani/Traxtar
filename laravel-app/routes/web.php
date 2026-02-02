<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AdminInvitationController;
use App\Http\Controllers\Admin\OrderManagementController;
use Illuminate\Support\Facades\Route;

// Stripe webhook (must be outside middleware and CSRF protection)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

// Health check route (for Render)
Route::get('/up', function () {
    return response()->json(['status' => 'ok'], 200);
})->name('health');

// Public routes
Route::get('/', [HomeController::class, 'landing'])->name('home');

// Product routes (public)
Route::get('/products', [ProductController::class, 'shop'])->name('products.shop');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Admin invitation acceptance (public - no auth required)
Route::get('/admin/invitations/accept/{token}', [AdminInvitationController::class, 'accept'])->name('admin.invitations.accept');
Route::post('/admin/invitations/accept/{token}', [AdminInvitationController::class, 'processAcceptance'])->name('admin.invitations.accept.post');

// Cart routes (public viewing, but modifications require auth)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

// Protected cart and checkout routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'customer'])->group(function () {
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

// Authenticated routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    
    // Customer routes
    Route::middleware(['customer'])->group(function () {
        Route::get('/customer/dashboard', [HomeController::class, 'customerDashboard'])->name('customer.dashboard');
        Route::get('/customer/profile', [\Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController::class, 'show'])->name('customer.profile');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/place', [OrderController::class, 'placeOrder'])->name('orders.place');
        
        // Payment routes
        Route::match(['get', 'post'], '/payment/checkout', [PaymentController::class, 'createCheckoutSession'])->name('payment.checkout');
        Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/failure/{order}', [PaymentController::class, 'failure'])->name('payment.failure');
        Route::get('/payment/cancel/{order}', [PaymentController::class, 'cancel'])->name('payment.cancel');
    });
    
    // Admin routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', [HomeController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/admin/analytics', function () {
            return view('admin.analytics');
        })->name('admin.analytics');
        
        // Admin product management
        Route::resource('admin/products', ProductController::class)->except(['show'])->names([
            'index' => 'admin.products.index',
            'create' => 'admin.products.create',
            'store' => 'admin.products.store',
            'edit' => 'admin.products.edit',
            'update' => 'admin.products.update',
            'destroy' => 'admin.products.destroy',
        ]);

        // Admin order management
        Route::get('/admin/orders', [OrderManagementController::class, 'index'])->name('admin.orders.index');
        Route::get('/admin/orders/{order}', [OrderManagementController::class, 'show'])->name('admin.orders.show');
        Route::patch('/admin/orders/{order}/status', [OrderManagementController::class, 'updateStatus'])->name('admin.orders.updateStatus');
        Route::post('/admin/orders/{order}/cancel', [OrderManagementController::class, 'cancel'])->name('admin.orders.cancel');

        // Admin management (Super Admin only)
        Route::middleware(['super-admin'])->group(function () {
            Route::get('/admin/admins', [AdminManagementController::class, 'index'])->name('admin.admins.index');
            Route::get('/admin/admins/invite', [AdminManagementController::class, 'create'])->name('admin.admins.invite');
            // Rate limit invitation routes (5 requests per minute) - abuse prevention
            Route::middleware('throttle:admin-invitations')->group(function () {
                Route::post('/admin/admins/invite', [AdminInvitationController::class, 'store'])->name('admin.admins.invite.store');
                Route::post('/admin/invitations/{invitation}/resend', [AdminInvitationController::class, 'resend'])->name('admin.invitations.resend');
            });
            Route::post('/admin/admins/{admin}/deactivate', [AdminManagementController::class, 'deactivate'])->name('admin.admins.deactivate');
            Route::post('/admin/admins/{admin}/activate', [AdminManagementController::class, 'activate'])->name('admin.admins.activate');
            Route::patch('/admin/admins/{admin}/role', [AdminManagementController::class, 'updateRole'])->name('admin.admins.updateRole');
            Route::delete('/admin/invitations/{invitation}', [AdminInvitationController::class, 'destroy'])->name('admin.invitations.destroy');
        });
    });
});
