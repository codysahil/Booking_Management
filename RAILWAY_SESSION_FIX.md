# Railway Deployment Fix Guide

## Critical Issues

### 1. SQLite on Ephemeral Storage ⚠️
**CRITICAL:** You're using SQLite (`DB_CONNECTION="sqlite"`) on Railway, which has ephemeral storage. Your entire database gets wiped on every deployment!

### 2. Session Configuration
The booking flow is experiencing session loss due to incorrect cookie session configuration.

## Root Cause
Railway's ephemeral filesystem doesn't persist SQLite databases or file-based sessions between deployments or container restarts.

## Solution Steps

### 1. 🚨 CRITICAL: Switch from SQLite to PostgreSQL

#### Add PostgreSQL Database on Railway:
1. In your Railway project dashboard, click **"New"** → **"Database"** → **"Add PostgreSQL"**
2. Railway will automatically create and link the database
3. It will add these environment variables automatically:
   - `DATABASE_URL`
   - `PGDATABASE`
   - `PGHOST`
   - `PGPASSWORD`
   - `PGPORT`
   - `PGUSER`

#### Update Laravel Database Variables:
Add/update these in Railway environment variables:
```
DB_CONNECTION=pgsql
DB_HOST=${PGHOST}
DB_PORT=${PGPORT}
DB_DATABASE=${PGDATABASE}
DB_USERNAME=${PGUSER}
DB_PASSWORD=${PGPASSWORD}
```

### 2. Fix Session Configuration

Update these Railway environment variables:

```
SESSION_DRIVER=cookie
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_LIFETIME=120
```

**Current Issues:**
- ❌ `SESSION_ENCRYPT="false"` - Should be `"true"` for cookie sessions
- ❌ `SESSION_SECURE_COOKIE` missing - Required for HTTPS

### 3. Run Migrations on Railway

After adding PostgreSQL and updating variables, Railway will redeploy. Then run:

```bash
php artisan migrate --force
php artisan db:seed --force
```

This will create all tables and seed sample data in your new PostgreSQL database.

### 4. Verify Configuration

Your Railway environment should have:
```
✅ APP_URL=https://bookingmanagement-production.up.railway.app
✅ DB_CONNECTION=pgsql
✅ SESSION_DRIVER=cookie
✅ SESSION_ENCRYPT=true
✅ SESSION_SECURE_COOKIE=true
```

## Why PostgreSQL Instead of SQLite?

| Feature | SQLite | PostgreSQL |
|---------|--------|------------|
| Storage | File-based | Network database |
| Railway Persistence | ❌ Wiped on deploy | ✅ Persistent |
| Concurrent Users | Limited | Excellent |
| Production Ready | ❌ No | ✅ Yes |
| Railway Cost | Free | Free (500MB) |

**Bottom Line:** SQLite is great for local development, but PostgreSQL is required for Railway production deployment.

## Testing

After applying the fix:

1. Go to your Railway URL
2. Select a branch and room
3. Select beds
4. Fill in the checkout form
5. Submit the booking
6. Verify you're redirected to the confirmation page with booking details

## Debugging

If issues persist, check Railway logs for:
- Session-related errors
- Database connection issues
- Migration status

You can also add logging to the `processPayment` method to track the flow:
```php
\Log::info('Session data:', session()->all());
\Log::info('Selected beds:', session('selected_bed_ids'));
```

## Current Status

❌ **CRITICAL:** Using SQLite on Railway (data loss on every deploy)
❌ Session encryption disabled
❌ SESSION_SECURE_COOKIE missing
⏳ Need to add PostgreSQL database
⏳ Need to update session environment variables
⏳ Need to run migrations on Railway

## Quick Checklist

- [ ] Add PostgreSQL database on Railway
- [ ] Update `DB_CONNECTION=pgsql` and related variables
- [ ] Update `SESSION_ENCRYPT=true`
- [ ] Add `SESSION_SECURE_COOKIE=true`
- [ ] Run `php artisan migrate --force` on Railway
- [ ] Run `php artisan db:seed --force` on Railway
- [ ] Test booking flow on Railway URL
