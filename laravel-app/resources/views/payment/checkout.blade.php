@extends('layouts.traxtar')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Complete Payment</h1>

    @if(session('success'))
        <div class="bg-green-500 text-white p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500 text-white p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Order Summary -->
    <div class="card p-6 mb-6">
        <h2 class="font-semibold mb-4">Order Summary</h2>
        <div class="space-y-2 mb-4">
            <div class="flex justify-between text-sm">
                <span>Order ID:</span>
                <span class="font-medium">#{{ $order->id }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Items:</span>
                <span>{{ $order->items->count() }} item(s)</span>
            </div>
        </div>
        <ul class="space-y-2 mb-4 border-t pt-4">
            @foreach($order->items as $item)
                <li class="flex justify-between text-sm">
                    <span>{{ $item->product_name }} × {{ $item->qty }}</span>
                    <span>LKR {{ number_format($item->price * $item->qty, 2) }}</span>
                </li>
            @endforeach
        </ul>
        <div class="border-t pt-4 flex justify-between font-semibold">
            <span>Total:</span>
            <span>LKR {{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <!-- Stripe Payment Form -->
    <div class="card p-6">
        <h2 class="font-semibold mb-4">Payment Details</h2>
        
        <form id="payment-form">
            <div id="payment-element">
                <!-- Stripe Elements will create form elements here -->
            </div>
            
            <div id="payment-message" class="hidden mt-4 p-4 rounded"></div>
            
            <div class="mt-6 flex gap-4">
                <button type="submit" id="submit" class="btn-primary flex-1">
                    <span id="button-text">Pay LKR {{ number_format($order->total, 2) }}</span>
                    <span id="spinner" class="hidden">Processing...</span>
                </button>
                <a href="{{ route('payment.cancel', $order) }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ $stripeKey }}');
    const clientSecret = '{{ $clientSecret }}';
    
    const elements = stripe.elements({
        clientSecret: clientSecret,
        appearance: {
            theme: 'stripe',
        },
    });
    
    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');
    
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    const paymentMessage = document.getElementById('payment-message');
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        setLoading(true);
        
        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: '{{ route('payment.success') }}?payment_intent={{ $payment->stripe_payment_intent_id }}&order_id={{ $order->id }}',
            },
        });
        
        if (error) {
            showMessage(error.message);
            setLoading(false);
        } else {
            // Payment succeeded, redirect will happen automatically
        }
    });
    
    function setLoading(isLoading) {
        if (isLoading) {
            submitButton.disabled = true;
            buttonText.classList.add('hidden');
            spinner.classList.remove('hidden');
        } else {
            submitButton.disabled = false;
            buttonText.classList.remove('hidden');
            spinner.classList.add('hidden');
        }
    }
    
    function showMessage(messageText, isError = true) {
        paymentMessage.textContent = messageText;
        paymentMessage.className = isError 
            ? 'mt-4 p-4 rounded bg-red-100 text-red-800 border border-red-300' 
            : 'mt-4 p-4 rounded bg-green-100 text-green-800 border border-green-300';
        paymentMessage.classList.remove('hidden');
        
        setTimeout(() => {
            paymentMessage.classList.add('hidden');
        }, 8000);
    }
    
    // Check payment status periodically if needed
    stripe.retrievePaymentIntent(clientSecret).then(({paymentIntent}) => {
        if (paymentIntent.status === 'succeeded') {
            window.location.href = '{{ route('payment.success') }}?payment_intent={{ $payment->stripe_payment_intent_id }}&order_id={{ $order->id }}';
        } else if (paymentIntent.status === 'requires_payment_method') {
            showMessage('Your payment was not successful, please try again.', true);
        }
    });
</script>
@endsection
