# Railway Debugging Steps

## Current Issue
Booking form submits but doesn't create booking on Railway (works locally).

## Step 1: Test Database Connection

Visit this URL on Railway:
```
https://bookingmanagement-production.up.railway.app/debug/db-test
```

**Expected Response:**
```json
{
  "status": "success",
  "database": "sqlite" or "pgsql",
  "counts": {
    "branches": 2,
    "customers": X,
    "bookings": X,
    "beds": X
  },
  "latest_booking": {...}
}
```

**If you see an error:** Database is not connected properly.

## Step 2: Check Railway Logs

After submitting a booking, check Railway logs for these messages:

### Success Path:
```
[INFO] Processing payment
[INFO] Booking created successfully
[INFO] Confirmation page loaded
```

### Error Path:
```
[ERROR] Booking failed: [error message]
```

## Step 3: Verify Environment Variables

Make sure these are set on Railway:

### Critical Variables:
```
✅ APP_KEY=base64:... (must be set!)
✅ APP_URL=https://bookingmanagement-production.up.railway.app
✅ DB_CONNECTION=pgsql (NOT sqlite!)
✅ SESSION_DRIVER=cookie
✅ SESSION_ENCRYPT=true
✅ SESSION_SECURE_COOKIE=true
```

### Database Variables (if using PostgreSQL):
```
✅ DB_HOST=${PGHOST}
✅ DB_PORT=${PGPORT}
✅ DB_DATABASE=${PGDATABASE}
✅ DB_USERNAME=${PGUSER}
✅ DB_PASSWORD=${PGPASSWORD}
```

## Step 4: Run Migrations on Railway

In Railway terminal:
```bash
php artisan migrate:status
```

**If migrations are not run:**
```bash
php artisan migrate --force
php artisan db:seed --force
```

## Step 5: Test Booking Flow

1. Go to homepage
2. Click "Book Now" on a branch
3. Select a room
4. Select beds (check the checkboxes)
5. Click "Proceed to Checkout"
6. Fill in phone number (10 digits)
7. Click "Confirm Booking"
8. **Check Railway logs immediately**

## Step 6: Manual Database Check

In Railway terminal:
```bash
php artisan tinker
```

Then run:
```php
// Check if branches exist
\App\Models\Branch::count()

// Check if beds exist
\App\Models\Bed::where('status', 'vacant')->count()

// Check latest booking
\App\Models\Booking::latest()->first()

// Check latest customer
\App\Models\Customer::latest()->first()
```

## Common Issues & Solutions

### Issue 1: "Session expired" error
**Cause:** Session not persisting
**Solution:** Set `SESSION_ENCRYPT=true` and `SESSION_SECURE_COOKIE=true`

### Issue 2: Booking form redirects to home
**Cause:** Validation failing or beds not found
**Solution:** Check Railway logs for validation errors

### Issue 3: 500 Internal Server Error
**Cause:** Database connection issue or missing APP_KEY
**Solution:** 
- Verify `APP_KEY` is set
- Check database connection with `/debug/db-test`

### Issue 4: No data in database
**Cause:** Using SQLite (ephemeral storage)
**Solution:** Switch to PostgreSQL:
1. Add PostgreSQL database in Railway
2. Update `DB_CONNECTION=pgsql`
3. Run migrations

### Issue 5: CSRF token mismatch
**Cause:** Session not working properly
**Solution:** 
- Clear cache: `php artisan config:clear`
- Verify `SESSION_SECURE_COOKIE=true`

## Quick Fix Checklist

- [ ] Database is PostgreSQL (not SQLite)
- [ ] Migrations have been run
- [ ] Seeder has been run (branches and beds exist)
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `APP_KEY` is set
- [ ] `/debug/db-test` returns success
- [ ] Railway logs show "Processing payment" message

## If Still Not Working

Share the following information:

1. **Output from `/debug/db-test`**
2. **Railway logs** (last 50 lines after submitting booking)
3. **Environment variables** (screenshot of Railway variables)
4. **Migration status** (output of `php artisan migrate:status`)

## Next Steps After Fix

Once bookings are working:

1. Remove the `/debug/db-test` route (security)
2. Test customer portal login
3. Test admin panel booking management
4. Add payment gateway integration
