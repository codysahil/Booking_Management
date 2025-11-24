# 🎉 Customer Portal - Complete Guide

## How Customers Login

### Login Credentials:
- **Username:** Customer ID (e.g., `SS-2025-0001`)
- **Password:** Their registered phone number (e.g., `9876543210`)

### Login URL:
```
https://your-domain.com/portal/login
```

Or click the "Login to Customer Portal" button on the confirmation page.

---

## Customer Journey

### 1. **After Booking**
When a customer completes a booking:
- They receive a **Customer ID** (Format: SS-YYYY-0001)
- They see it on the confirmation page
- They should save this ID for future login

### 2. **First Login**
- Go to `/portal/login`
- Enter Customer ID: `SS-2025-0001`
- Enter Password: Their phone number (e.g., `9876543210`)
- Click "Login to Portal"

### 3. **Customer Dashboard**
After login, customers can see:
- ✅ **Active Bookings** - Number of current bookings
- ✅ **Pending Dues** - Amount they need to pay (coming soon)
- ✅ **Total Payments** - All completed payments
- ✅ **Booking Details** - All their bookings with:
  - Booking reference
  - Branch name
  - Room & bed number
  - Check-in date
  - Status (Pending/Confirmed/Checked In)
  - Advance paid

### 4. **Quick Actions** (Coming Soon)
- 💰 **View & Pay Dues** - Pay rent, EB bills, fines online
- 📝 **Raise Requests** - Room swap, vacation notice, maintenance, refund

---

## Features Available Now

### ✅ Implemented:
1. **Customer Login** - Using Customer ID + Phone
2. **Dashboard** - Overview of bookings and stats
3. **View Bookings** - See all booking details
4. **Logout** - Secure logout functionality

### 🚧 Coming Soon (Phase 4):
1. **View Dues** - See pending rent, EB, fines
2. **Pay Online** - Payment gateway integration
3. **Raise Requests**:
   - Room swap request
   - Vacation notice
   - Maintenance/service request
   - Refund request
4. **Profile Page** - View/update personal details
5. **Download Receipts** - PDF receipts for payments
6. **Payment History** - All past transactions

---

## How to Access

### For Customers:
1. **From Homepage:** Click "Login" button in navbar
2. **From Confirmation Page:** Click "Login to Customer Portal" button
3. **Direct URL:** `/portal/login`

### Login Details:
- **Customer ID:** Shown on booking confirmation
- **Password:** Their registered phone number

---

## Admin View

Admins can see all customer bookings in:
```
/admin/bookings
```

Features:
- Search by name, phone, booking ref, customer ID
- Filter by status and branch
- Update booking status
- View all customer details

---

## Security Features

1. ✅ **Separate Authentication Guard** - Customers and admins use different sessions
2. ✅ **Password Hashing** - Phone numbers are hashed using bcrypt
3. ✅ **Session Management** - Secure session handling
4. ✅ **Remember Me** - Optional persistent login
5. ✅ **CSRF Protection** - All forms protected

---

## Testing the Portal

### Test Customer Login:
1. Make a booking on the website
2. Note the Customer ID (e.g., SS-2025-0001)
3. Go to `/portal/login`
4. Enter Customer ID and phone number
5. You should see the dashboard with your booking

### Sample Test Data:
If you have a customer with:
- Customer ID: `SS-2025-0001`
- Phone: `9876543210`

Login with:
- Username: `SS-2025-0001`
- Password: `9876543210`

---

## Troubleshooting

### "Credentials do not match"
- ✅ Check Customer ID is correct (case-sensitive)
- ✅ Ensure phone number matches exactly
- ✅ Verify customer exists in database

### Can't see bookings
- ✅ Check if bookings are linked to customer_id
- ✅ Verify booking status is not 'cancelled'

### Session issues
- ✅ Clear browser cache
- ✅ Try incognito mode
- ✅ Check session configuration in `.env`

---

## Next Steps

### Phase 4 - Customer Portal Enhancement:
1. **Dues Management**
   - View pending rent
   - View EB bills
   - View fines
   - Pay online

2. **Request System**
   - Room swap requests
   - Vacation notices (1 month minimum)
   - Maintenance requests with image upload
   - Refund requests (after vacation + dues cleared)
   - Track request status

3. **Profile Management**
   - View uploaded photo & ID proof
   - Update contact details
   - Change password
   - Download receipts

4. **Notifications**
   - Rent reminders
   - EB bill alerts
   - Request status updates
   - Payment confirmations

---

## File Structure

```
app/
├── Http/Controllers/Customer/
│   ├── AuthController.php          ✅ Login/Logout
│   └── DashboardController.php     ✅ Dashboard
├── Models/
│   └── Customer.php                ✅ Customer model
resources/views/
├── customer/
│   ├── auth/
│   │   └── login.blade.php         ✅ Login page
│   └── dashboard.blade.php         ✅ Dashboard
routes/
└── web.php                         ✅ Customer routes
config/
└── auth.php                        ✅ Customer guard
```

---

**Last Updated:** November 24, 2025
**Status:** ✅ Customer Portal Login & Dashboard Complete!
