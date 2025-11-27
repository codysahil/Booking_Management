# Payment System Implementation Guide

## Overview
Complete payment management system for monthly rent, EB charges, and custom dues with online payment integration.

## Database Structure Created

### 1. Monthly Charges Table
- Tracks monthly rent + EB charges for each customer
- Fields: rent_amount, eb_amount, other_charges, total_amount
- Status: pending, paid, overdue
- Unique per customer per month

### 2. Dues Table
- Custom charges (damage, late fees, maintenance, etc.)
- Flexible amount and description
- Status: pending, paid

## Features to Implement

### Admin Features
1. **Monthly Charge Management**
   - Generate monthly charges for all active customers
   - Set/update rent amount per customer
   - Add EB charges monthly
   - Add other charges with description
   - View payment history

2. **Due Management**
   - Create custom dues for specific customers
   - Set due dates
   - Track payment status
   - Add descriptions

3. **Rent Increase**
   - Update rent amount for specific customer
   - Apply from next billing cycle

### Customer Features
1. **View Pending Payments**
   - See all pending monthly charges
   - See all pending dues
   - Total amount due

2. **Online Payment**
   - Pay via Razorpay/PhonePe/UPI
   - Select which charges to pay
   - Get payment confirmation
   - Download receipt

## Implementation Steps

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Admin Controllers (Already Created)
- `MonthlyChargeController` - Manage monthly charges
- `DueController` - Manage custom dues

### Step 3: Customer Payment Controller (Already Created)
- `PaymentController` - Handle online payments

### Step 4: Payment Gateway Integration
Choose one:
- **Razorpay** (Recommended - Easy integration)
- **PhonePe** (UPI focused)
- **Cashfree** (Alternative)

### Step 5: Admin Views Needed
1. `/admin/charges` - List all monthly charges
2. `/admin/charges/generate` - Generate charges for month
3. `/admin/charges/{customer}` - Customer-specific charges
4. `/admin/dues` - List all dues
5. `/admin/dues/create` - Create new due

### Step 6: Customer Views Needed
1. `/customer/payments` - View all pending payments
2. `/customer/payments/pay` - Payment page
3. `/customer/payments/history` - Payment history

## Payment Gateway Setup (Razorpay Example)

### 1. Install Package
```bash
composer require razorpay/razorpay
```

### 2. Add to .env
```
RAZORPAY_KEY=your_key_id
RAZORPAY_SECRET=your_secret_key
```

### 3. Payment Flow
1. Customer selects charges to pay
2. System creates Razorpay order
3. Customer completes payment
4. Webhook updates payment status
5. Send confirmation email/SMS

## Monthly Charge Generation (Automated)

### Create Artisan Command
```bash
php artisan make:command GenerateMonthlyCharges
```

### Schedule in Kernel.php
```php
$schedule->command('charges:generate')->monthlyOn(1, '00:00');
```

This will auto-generate charges on 1st of every month.

## Next Steps

1. **Immediate**: Run migrations
2. **Phase 1**: Build admin charge management UI
3. **Phase 2**: Build customer payment UI
4. **Phase 3**: Integrate payment gateway
5. **Phase 4**: Add automated charge generation
6. **Phase 5**: Add email/SMS notifications

## Security Considerations

- Validate all payment amounts
- Use HTTPS for payment pages
- Store transaction IDs
- Log all payment activities
- Implement payment verification
- Handle payment failures gracefully

## Testing

- Test with Razorpay test mode
- Verify charge calculations
- Test payment success/failure flows
- Check webhook handling
- Verify receipt generation
