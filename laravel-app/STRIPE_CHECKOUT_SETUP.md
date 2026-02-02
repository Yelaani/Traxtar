# Stripe Checkout Setup Guide

## Step 2: Configure Environment Variables

### 2.1 Get Your Stripe API Keys

1. **Log in to Stripe Dashboard**: https://dashboard.stripe.com/
2. **Switch to Test Mode** (for development) or **Live Mode** (for production)
3. **Navigate to**: Developers → API keys

### 2.2 Add Keys to Your `.env` File

Open your `.env` file in the `laravel-app` directory and add/update these lines:

```env
# Stripe Configuration
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
STRIPE_CURRENCY=usd
```

**Important Notes:**
- **Test Mode**: Use keys starting with `pk_test_` and `sk_test_`
- **Live Mode**: Use keys starting with `pk_live_` and `sk_live_`
- **Never commit** your `.env` file to version control
- The `STRIPE_WEBHOOK_SECRET` will be obtained in Step 3

### 2.3 Example `.env` Configuration

```env
# For Test Mode (Development)
STRIPE_KEY=pk_test_51AbCdEfGhIjKlMnOpQrStUvWxYz1234567890
STRIPE_SECRET=sk_test_51AbCdEfGhIjKlMnOpQrStUvWxYz1234567890
STRIPE_WEBHOOK_SECRET=whsec_1234567890abcdefghijklmnopqrstuvwxyz
STRIPE_CURRENCY=usd
```

---

## Step 3: Configure Stripe Webhook

### 3.1 Create Webhook Endpoint in Stripe Dashboard

1. **Log in to Stripe Dashboard**: https://dashboard.stripe.com/
2. **Navigate to**: Developers → Webhooks
3. **Click**: "Add endpoint" or "+ Add endpoint"

### 3.2 Configure Webhook Settings

**Endpoint URL:**
```
https://your-domain.com/stripe/webhook
```

**For Local Development (using Stripe CLI):**
```
stripe listen --forward-to localhost:8000/stripe/webhook
```

**Events to Send:**
Select these specific events:
- `checkout.session.completed`
- `checkout.session.async_payment_succeeded`
- `checkout.session.async_payment_failed`
- `payment_intent.succeeded` (legacy support)
- `payment_intent.payment_failed` (legacy support)
- `payment_intent.canceled` (legacy support)

### 3.3 Get Webhook Signing Secret

1. After creating the webhook, click on it to view details
2. Find the **"Signing secret"** section
3. Click **"Reveal"** to show the secret (starts with `whsec_`)
4. Copy the secret and add it to your `.env` file:

```env
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

### 3.4 Test Webhook (Optional)

You can test the webhook using Stripe CLI:

```bash
# Install Stripe CLI (if not installed)
# Windows: Download from https://github.com/stripe/stripe-cli/releases
# Mac: brew install stripe/stripe-cli/stripe
# Linux: See Stripe CLI documentation

# Login to Stripe
stripe login

# Forward webhooks to local server
stripe listen --forward-to localhost:8000/stripe/webhook

# Trigger a test event
stripe trigger checkout.session.completed
```

---

## Testing the Integration

### Test Payment Flow

1. **Add items to cart** on your application
2. **Go to checkout** and fill in shipping details
3. **Click "Proceed to Checkout"** - should redirect to Stripe Checkout
4. **Use Stripe test card**:
   - Card Number: `4242 4242 4242 4242`
   - Expiry: Any future date (e.g., `12/25`)
   - CVC: Any 3 digits (e.g., `123`)
   - ZIP: Any 5 digits (e.g., `12345`)
5. **Complete payment** - should redirect to success page
6. **Check webhook** - verify payment status updated in database

### Test Failure Scenarios

Use these test cards to simulate failures:
- **Card declined**: `4000 0000 0000 0002`
- **Insufficient funds**: `4000 0000 0000 9995`
- **Expired card**: `4000 0000 0000 0069`

---

## Troubleshooting

### Issue: "No API key provided"
**Solution**: Check that `STRIPE_SECRET` is set in `.env` and run `php artisan config:clear`

### Issue: Webhook not receiving events
**Solution**: 
- Verify webhook URL is correct and accessible
- Check webhook secret matches in `.env`
- Ensure webhook events are selected in Stripe Dashboard
- Check Laravel logs: `storage/logs/laravel.log`

### Issue: Payment succeeds but status not updated
**Solution**:
- Verify webhook is configured correctly
- Check webhook secret in `.env`
- Review webhook logs in Stripe Dashboard
- Check Laravel application logs

### Issue: CSRF token mismatch on webhook
**Solution**: This is normal - webhook route is already excluded from CSRF protection in `routes/web.php`

---

## Production Checklist

Before going live:

- [ ] Switch to **Live Mode** in Stripe Dashboard
- [ ] Update `.env` with **live** API keys (pk_live_* and sk_live_*)
- [ ] Create **production webhook** endpoint
- [ ] Update webhook URL to production domain
- [ ] Test complete payment flow in production
- [ ] Monitor webhook events in Stripe Dashboard
- [ ] Set up error monitoring/alerts
- [ ] Review and test failure scenarios

---

## Additional Resources

- **Stripe Documentation**: https://stripe.com/docs/checkout
- **Stripe Test Cards**: https://stripe.com/docs/testing
- **Stripe Webhooks Guide**: https://stripe.com/docs/webhooks
- **Stripe Dashboard**: https://dashboard.stripe.com/

---

## Quick Reference

### Environment Variables Required:
```env
STRIPE_KEY=pk_test_...          # Publishable key
STRIPE_SECRET=sk_test_...        # Secret key
STRIPE_WEBHOOK_SECRET=whsec_...  # Webhook signing secret
STRIPE_CURRENCY=usd              # Currency code
```

### Webhook Events Required:
- `checkout.session.completed`
- `checkout.session.async_payment_succeeded`
- `checkout.session.async_payment_failed`

### Webhook URL Format:
```
https://your-domain.com/stripe/webhook
```
