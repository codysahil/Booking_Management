# Razorpay Payment Gateway Integration Guide

## Step 1: Install Razorpay PHP SDK

```bash
composer require razorpay/razorpay
```

## Step 2: Get Razorpay Credentials

1. Sign up at https://razorpay.com
2. Go to Settings → API Keys
3. Generate Test/Live Keys
4. Copy Key ID and Key Secret

## Step 3: Add to .env

```env
RAZORPAY_KEY=rzp_test_xxxxxxxxxxxxx
RAZORPAY_SECRET=xxxxxxxxxxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
```

## Step 4: Add to config/services.php

```php
'razorpay' => [
    'key' => env('RAZORPAY_KEY'),
    'secret' => env('RAZORPAY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
],
```

## Payment Flow

1. Customer selects charges to pay
2. System creates Razorpay order
3. Customer completes payment on Razorpay
4. Razorpay sends webhook notification
5. System verifies and marks payment as paid
6. Customer receives confirmation

## Testing

Use Razorpay test cards:
- Success: 4111 1111 1111 1111
- Failure: 4000 0000 0000 0002
- CVV: Any 3 digits
- Expiry: Any future date

## Webhook Setup

1. Go to Razorpay Dashboard → Webhooks
2. Add webhook URL: https://yourdomain.com/api/razorpay/webhook
3. Select events: payment.captured, payment.failed
4. Copy webhook secret to .env

## Security

- Always verify payment signature
- Use HTTPS in production
- Store transaction IDs
- Log all payment activities
- Handle payment failures gracefully
