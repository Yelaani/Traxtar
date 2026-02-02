<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    /**
     * Initialize Stripe with API key.
     */
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create Stripe Checkout Session and redirect to Stripe's hosted checkout page.
     * 
     * This method creates a Stripe Checkout Session with the order details,
     * stores the session ID in the database, and redirects the user to Stripe's
     * secure checkout page where they can complete payment.
     * 
     * Supports both GET (redirect from order placement) and POST requests.
     */
    public function createCheckoutSession(Request $request): RedirectResponse
    {
        $this->authorize('customer-access');

        // Get order_id from request or session
        $orderId = $request->input('order_id') ?? session()->get('order_id');
        
        if (!$orderId) {
            return redirect()->route('orders.index')
                ->with('error', 'No order found. Please place an order first.');
        }

        $order = Order::with('items')->findOrFail($orderId);

        // Verify order belongs to authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Check if order already has a successful payment
        if ($order->payment()->where('status', 'succeeded')->exists()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'This order has already been paid.');
        }

        // Check if order is still pending
        if (!$order->isPending()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'This order cannot be paid.');
        }

        try {
            $currency = config('services.stripe.currency', 'usd');
            
            // Build line items for Stripe Checkout
            $lineItems = [];
            foreach ($order->items as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $item->product_name,
                            'description' => "Order #{$order->id} - {$item->product_name}",
                        ],
                        'unit_amount' => (int) ($item->price * 100), // Convert to cents
                    ],
                    'quantity' => $item->qty,
                ];
            }

            // Create Stripe Checkout Session
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order->id,
                'cancel_url' => route('payment.cancel', $order),
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                ],
                'customer_email' => auth()->user()->email,
            ]);

            // Create or update payment record in database
            $payment = Payment::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'status' => 'pending',
                ],
                [
                    'stripe_checkout_session_id' => $checkoutSession->id,
                    'amount' => $order->total,
                    'currency' => $currency,
                    'metadata' => [
                        'stripe_checkout_session' => $checkoutSession->toArray(),
                        'created_at' => now()->toIso8601String(),
                    ],
                ]
            );

            // Log successful checkout session creation
            Log::info('Stripe Checkout Session created', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'checkout_session_id' => $checkoutSession->id,
                'amount' => $order->total,
            ]);

            // Redirect to Stripe Checkout
            return redirect($checkoutSession->url);

        } catch (ApiErrorException $e) {
            // Log detailed error information
            Log::error('Stripe Checkout Session creation failed', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'stripe_error' => $e->getStripeCode() ?? 'unknown',
                'http_status' => $e->getHttpStatus() ?? 'unknown',
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Payment initialization failed. Please try again or contact support if the problem persists.');
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Unexpected error creating Stripe Checkout Session', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'An unexpected error occurred. Please try again or contact support.');
        }
    }

    /**
     * Handle successful payment redirect from Stripe Checkout.
     * 
     * This method is called when Stripe redirects the user back after
     * a successful payment. It verifies the payment status and updates
     * the payment record accordingly.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $this->authorize('customer-access');

        $validated = $request->validate([
            'session_id' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('items')->findOrFail($validated['order_id']);

        // Verify order belongs to authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        try {
            // Retrieve checkout session from Stripe
            $checkoutSession = Session::retrieve($validated['session_id']);

            // Find payment record
            $payment = Payment::where('stripe_checkout_session_id', $checkoutSession->id)
                ->where('order_id', $order->id)
                ->first();

            if (!$payment) {
                // Payment record not found, create it
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'stripe_checkout_session_id' => $checkoutSession->id,
                    'amount' => $order->total,
                    'currency' => $checkoutSession->currency ?? config('services.stripe.currency', 'usd'),
                    'status' => $checkoutSession->payment_status === 'paid' ? 'succeeded' : 'pending',
                    'payment_method' => 'card',
                    'metadata' => [
                        'stripe_checkout_session' => $checkoutSession->toArray(),
                        'verified_at' => now()->toIso8601String(),
                    ],
                ]);
            } else {
                // Update existing payment record
                DB::beginTransaction();
                
                $paymentStatus = match($checkoutSession->payment_status) {
                    'paid' => 'succeeded',
                    'unpaid' => 'pending',
                    'no_payment_required' => 'succeeded',
                    default => 'pending',
                };

                $payment->update([
                    'status' => $paymentStatus,
                    'stripe_charge_id' => $checkoutSession->payment_intent ?? null,
                    'payment_method' => 'card',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'stripe_checkout_session' => $checkoutSession->toArray(),
                        'verified_at' => now()->toIso8601String(),
                    ]),
                ]);

                DB::commit();
            }

            // Log successful payment
            Log::info('Payment succeeded via Stripe Checkout', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'checkout_session_id' => $checkoutSession->id,
                'payment_status' => $checkoutSession->payment_status,
            ]);

            // Clear session
            session()->forget('order_id');

            // Show success page
            return view('payment.success', [
                'order' => $order,
                'payment' => $payment,
            ]);

        } catch (ApiErrorException $e) {
            // Log error with detailed context
            Log::error('Stripe Checkout Session verification failed', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'session_id' => $validated['session_id'],
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'stripe_error' => $e->getStripeCode() ?? 'unknown',
            ]);

            // Redirect to failure page
            return redirect()->route('payment.failure', ['order' => $order])
                ->with('error', 'Payment verification failed. Please contact support if you were charged.');
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Unexpected error verifying Stripe Checkout Session', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'session_id' => $validated['session_id'],
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
            ]);

            return redirect()->route('payment.failure', ['order' => $order])
                ->with('error', 'An unexpected error occurred. Please contact support.');
        }
    }

    /**
     * Handle failed payment redirect from Stripe Checkout.
     * 
     * This method is called when a payment fails or is cancelled.
     * It logs the failure and displays a user-friendly error message.
     */
    public function failure(Request $request, Order $order): View
    {
        $this->authorize('customer-access');

        // Verify order belongs to authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Get the latest payment for this order
        $payment = $order->payment()->latest()->first();

        // Try to retrieve checkout session if available
        $checkoutSession = null;
        $failureReason = session('error', 'Payment was not completed successfully.');

        if ($payment && $payment->stripe_checkout_session_id) {
            try {
                $checkoutSession = Session::retrieve($payment->stripe_checkout_session_id);
                
                // Update payment status if needed
                if ($checkoutSession->payment_status === 'unpaid') {
                    $payment->update([
                        'status' => 'failed',
                        'failure_reason' => $checkoutSession->payment_intent->last_payment_error->message ?? 'Payment failed',
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'stripe_checkout_session' => $checkoutSession->toArray(),
                            'failed_at' => now()->toIso8601String(),
                        ]),
                    ]);

                    // Log failed transaction
                    Log::warning('Payment failed via Stripe Checkout', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'checkout_session_id' => $payment->stripe_checkout_session_id,
                        'payment_status' => $checkoutSession->payment_status,
                        'failure_reason' => $payment->failure_reason,
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the page
                Log::error('Error retrieving checkout session for failure page', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('payment.failure', [
            'order' => $order,
            'payment' => $payment,
            'failureReason' => $failureReason,
        ]);
    }

    /**
     * Handle payment cancellation.
     * 
     * This method is called when the user cancels the payment
     * on the Stripe Checkout page.
     */
    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('customer-access');

        // Verify order belongs to authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Clear session
        session()->forget('order_id');

        // Log cancellation
        Log::info('Payment cancelled by user', [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('orders.show', $order)
            ->with('info', 'Payment cancelled. You can try again later.');
    }
}
