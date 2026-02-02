<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhook events.
     */
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook: Invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook: Invalid signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'checkout.session.async_payment_succeeded':
                $this->handleCheckoutSessionAsyncPaymentSucceeded($event->data->object);
                break;

            case 'checkout.session.async_payment_failed':
                $this->handleCheckoutSessionAsyncPaymentFailed($event->data->object);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'payment_intent.canceled':
                $this->handlePaymentIntentCanceled($event->data->object);
                break;

            default:
                Log::info('Stripe webhook: Unhandled event type', ['type' => $event->type]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Handle completed checkout session.
     * This is the primary webhook event for Stripe Checkout payments.
     */
    private function handleCheckoutSessionCompleted($checkoutSession)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::where('stripe_checkout_session_id', $checkoutSession->id)->first();

            if ($payment) {
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
                        'webhook_received_at' => now()->toIso8601String(),
                        'stripe_checkout_session' => $checkoutSession->toArray(),
                    ]),
                ]);

                DB::commit();

                Log::info('Checkout session completed via webhook', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'checkout_session_id' => $checkoutSession->id,
                    'payment_status' => $checkoutSession->payment_status,
                ]);
            } else {
                Log::warning('Checkout session completed but payment record not found', [
                    'checkout_session_id' => $checkoutSession->id,
                    'order_id' => $checkoutSession->metadata->order_id ?? null,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling checkout.session.completed webhook', [
                'checkout_session_id' => $checkoutSession->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle async payment succeeded for checkout session.
     */
    private function handleCheckoutSessionAsyncPaymentSucceeded($checkoutSession)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::where('stripe_checkout_session_id', $checkoutSession->id)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'succeeded',
                    'stripe_charge_id' => $checkoutSession->payment_intent ?? null,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'webhook_received_at' => now()->toIso8601String(),
                        'stripe_checkout_session' => $checkoutSession->toArray(),
                    ]),
                ]);

                DB::commit();

                Log::info('Checkout session async payment succeeded via webhook', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'checkout_session_id' => $checkoutSession->id,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling checkout.session.async_payment_succeeded webhook', [
                'checkout_session_id' => $checkoutSession->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle async payment failed for checkout session.
     */
    private function handleCheckoutSessionAsyncPaymentFailed($checkoutSession)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::where('stripe_checkout_session_id', $checkoutSession->id)->first();

            if ($payment) {
                $failureReason = 'Async payment failed';
                if (isset($checkoutSession->payment_intent)) {
                    try {
                        $paymentIntent = \Stripe\PaymentIntent::retrieve($checkoutSession->payment_intent);
                        $failureReason = $paymentIntent->last_payment_error->message ?? $failureReason;
                    } catch (\Exception $e) {
                        // Ignore error retrieving payment intent
                    }
                }

                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $failureReason,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'webhook_received_at' => now()->toIso8601String(),
                        'stripe_checkout_session' => $checkoutSession->toArray(),
                    ]),
                ]);

                DB::commit();

                // Log failed transaction with detailed context
                Log::warning('Checkout session async payment failed via webhook', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'checkout_session_id' => $checkoutSession->id,
                    'failure_reason' => $failureReason,
                    'user_id' => $payment->order->user_id ?? null,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling checkout.session.async_payment_failed webhook', [
                'checkout_session_id' => $checkoutSession->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle successful payment intent (legacy support).
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'succeeded',
                    'stripe_charge_id' => $paymentIntent->charges->data[0]->id ?? null,
                    'payment_method' => $paymentIntent->payment_method_types[0] ?? null,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'webhook_received_at' => now()->toIso8601String(),
                        'stripe_payment_intent' => $paymentIntent->toArray(),
                    ]),
                ]);

                // Order status remains 'pending' until admin confirms it manually
                // This allows admin to review order and verify stock before confirmation
                // Stock reduction happens on admin confirmation, not on payment success
                // $order = $payment->order;
                // if ($order && $order->status === 'pending') {
                //     $order->update(['status' => 'confirmed']);
                // }

                DB::commit();

                Log::info('Payment succeeded via webhook', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling payment_intent.succeeded webhook', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle failed payment intent.
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Payment failed',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'webhook_received_at' => now()->toIso8601String(),
                        'stripe_payment_intent' => $paymentIntent->toArray(),
                    ]),
                ]);

                DB::commit();

                // Log failed transaction with detailed context
                Log::warning('Payment failed via webhook', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'failure_reason' => $payment->failure_reason,
                    'user_id' => $payment->order->user_id ?? null,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling payment_intent.payment_failed webhook', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle canceled payment intent.
     */
    private function handlePaymentIntentCanceled($paymentIntent)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'cancelled',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'webhook_received_at' => now()->toIso8601String(),
                        'stripe_payment_intent' => $paymentIntent->toArray(),
                    ]),
                ]);

                DB::commit();

                Log::info('Payment canceled via webhook', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling payment_intent.canceled webhook', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
