# Hostel / PG Management System

A complete booking, billing and resident-management platform for a hostel or
PG (paying-guest) business, built on Laravel 12. It covers the whole
lifecycle: a public booking website, an admin back office for staff, and a
self-service portal for residents.

## Features

**Public booking site**
- Branch and room listings with photos, live bed availability, and a
  multi-bed group booking flow with a 10-minute hold on selected beds.
- Checkout collects the resident's details, requires accepting the
  Terms & Conditions, and creates the booking with a pending advance.
- A signed confirmation page (not guessable/sequential) shows the booking
  and can optionally take the advance payment online via Razorpay, or leave
  it to be collected at check-in.

**Admin panel** (`/admin`, staff accounts: **Owner/Admin** and **Manager**)
- Branches, rooms and beds; a homepage image slider.
- Bookings, walk-in and online check-in with photo/ID upload.
- Monthly rent charges — generate, edit, mark paid, and per-customer history.
- Dues — one-off fines, EB, damage, or other charges per customer.
- Expenses — categorized, filterable, with receipt upload and CSV export.
- Payment ledger with printable receipts for every payment collected,
  whether cash, UPI/card via Razorpay, or recorded manually.
- Reports: revenue/occupancy dashboard with CSV export, and a dedicated
  Profit & Loss report (income vs. expenses by category, branch filter).
- Resident requests inbox (room swap, vacation notice, refund, service,
  complaint) with responses emailed back to the resident.
- Announcements shown on residents' dashboards, and an in-panel
  notification centre with an unread badge for new bookings, requests and
  payments.
- Dashboard: occupancy, collected this month, pending/overdue dues,
  expenses, net income, a 6-month income-vs-expense chart, recent bookings
  and open requests — all filterable by branch.
- **Settings** (Owner only): branding (name, tagline, logo), contact
  details, billing rules (advance amount, rent due day, late fee, notice
  period), receipt footer/GSTIN, and the Terms & Conditions text.
- **Team** (Owner only): add/deactivate Manager accounts. Managers can run
  day-to-day operations but cannot access Settings or Team.

**Resident portal** (`/customer`, sign in with the Customer ID issued at
booking)
- Dashboard with active booking, pending dues and announcements.
- Pay rent/dues online via Razorpay, with a full payment history and
  printable receipts.
- Submit and track requests (swap, vacation, refund, service, complaint),
  with the vacation type enforcing the configured notice period.

**Scheduled jobs** (already wired into `bootstrap/app.php`)
- `charges:generate` — creates next month's rent charge for every active
  booking (1st of the month).
- `charges:mark-overdue` — flags unpaid charges past their due date (daily).

## Tech stack

- PHP 8.2+, Laravel 12
- SQLite by default (works out of the box); MySQL/PostgreSQL supported via
  standard Laravel `DB_*` env vars
- Tailwind CSS 4 + Vite, Alpine.js, Chart.js (CDN)
- [Razorpay](https://razorpay.com) for online payments (optional — the app
  runs fully on cash/manual payments if it isn't configured)
- [Cloudinary](https://cloudinary.com) for photo/document storage (optional
  — falls back to local/public disk storage)

## Requirements

- PHP >= 8.2 with the extensions Laravel needs (`ext-mbstring`, `ext-pdo`,
  `ext-sqlite3` or your chosen DB driver, etc.)
- Composer 2
- Node.js 18+ and npm

## Local installation

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite   # only if using the default SQLite setup
php artisan migrate

php artisan db:seed --class=AdminSeeder   # creates the owner account
# Optional demo data (branches/rooms/beds and homepage slides):
php artisan db:seed --class=BranchSeeder
php artisan db:seed --class=HeroSliderSeeder

php artisan storage:link
npm run build   # or `npm run dev` while developing

php artisan serve
```

Sign in to the admin panel at `/admin/login` with the email/password from
`ADMIN_EMAIL` / `ADMIN_PASSWORD` (defaults to `admin@honeybees.com` /
`admin123` — **change these in your `.env` before seeding**, and change the
password again after first login). `AdminSeeder` never overwrites an
existing user, so re-running it on a live database is always safe.

> `BranchSeeder` ships with the demo hostel's real branch names, addresses
> and room layout from the original deployment. Replace it with your own
> branches/rooms (via the admin panel, or by editing the seeder) before
> taking a store live — it only runs once (it skips itself if any branch
> already exists), so it's safe to leave in place either way.

## Running tests

```bash
php artisan test
```

The suite uses an in-memory SQLite database (see `phpunit.xml`) and covers
the booking flow (including the double-booking guard and group bookings),
staff/manager access control, payments, expenses, dues, requests,
announcements, settings/team, and the scheduled commands.

## Environment variables

| Variable | Purpose |
| --- | --- |
| `APP_URL` | Public base URL — required for correct signed links (booking confirmation) and asset URLs. |
| `DB_CONNECTION`, `DB_*` | Database connection. Defaults to SQLite; set the usual Laravel vars for MySQL/PostgreSQL. |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Owner account created by `AdminSeeder`. |
| `RAZORPAY_KEY`, `RAZORPAY_SECRET` | Enables online payments (checkout, rent/dues payment, advance payment) when both are set. |
| `RAZORPAY_WEBHOOK_SECRET` | Required for the `/webhook/razorpay` endpoint to accept events — set this to the secret configured on the same webhook in the Razorpay dashboard. Without it, the webhook refuses all events (it never falls back to accepting unverified ones). |
| `HOSTEL_NAME`, `HOSTEL_TAGLINE`, `HOSTEL_PHONE`, `HOSTEL_EMAIL`, `HOSTEL_WHATSAPP`, `HOSTEL_ADDRESS` | Starting values for branding/contact shown on the site, until the Owner saves them from Admin → Settings (which then takes over). |
| `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET` | Optional — route customer/employee photo and ID-proof uploads to Cloudinary instead of local storage. |
| `MAIL_*` | Needed to actually deliver resident-facing emails (e.g. `RequestStatusChanged`) and password resets; defaults to logging mail to the log file. |
| `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE` | Standard Laravel infrastructure config — the `database` driver used by default needs the `migrate` step above. |

See `.env.example` for the full list with sensible local defaults.

## Deployment

The repo includes a ready-to-use [Railway](https://railway.app) setup
(`railway.json`, `nixpacks.toml`, `Procfile`, `start.sh`): on each deploy it
clears caches, links storage, runs `migrate --force` and `db:seed --force`
(safe to repeat — every seeder here is idempotent), then starts
`php artisan serve`. To deploy elsewhere, reproduce the same steps:

```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache   # optional once your env vars are finalized
```

**Scheduler**: `charges:generate` and `charges:mark-overdue` only run if
something invokes Laravel's scheduler. Point a real cron job (or your
host's scheduled-task feature) at:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

A single long-running web dyno (as in `start.sh`) does **not** trigger this
on its own — add a separate cron/worker process on your host, or run the
two commands directly on your own schedule if you'd rather not run the
scheduler at all.

## How money and occupancy actually flow

This app is built around how a PG/hostel actually runs day to day, not like a
hotel booking engine:

- **Billing is monthly, not nightly.** Residents don't get a per-night rate —
  `charges:generate` creates one `MonthlyCharge` per resident per calendar
  month (on the 1st), and `charges:mark-overdue` flags unpaid ones daily. A
  booking's `check_in_date` just starts the stay; nothing bills per night.

- **Only staff can start or end a stay.** Moving a resident in (`Admin\CustomerController@store`,
  which sets the bed to `occupied`) or out (`@deactivate`, sets it back to
  `vacant`) requires a signed-in staff account behind the `admin` middleware.
  There is no self-service check-in or move-out anywhere in the resident
  portal — a resident paying online never flips a bed to occupied themselves,
  a staff member always does that in person against their ID.

- **Cash and online payments land in the same place, instantly — there is no
  separate "sync".** `app/Services/PaymentRecorder.php` is the only code path
  that writes to the `payments` table, whether it's a cash rent payment a
  staff member marks paid at the desk, a walk-in advance, or an online
  Razorpay payment (browser callback or webhook). The dashboard, Payment
  History, receipts and P&L report all read that one table live — recording
  a cash payment updates every one of those screens the same instant a
  staff member clicks "Mark Paid", the same as an online payment does.

- **An online booking has to actually be paid for; a walk-in doesn't.** A
  stranger booking on the public website (`Public\BookingController@processPayment`)
  isn't the same trust level as someone standing at the desk. Once Razorpay
  is configured, an online booking starts as `pending_payment` and holds its
  bed for a configurable window (Admin → Settings → Billing Rules, default 30
  minutes) — if payment never completes, `bookings:release-expired` (runs
  every 5 minutes) cancels it and frees the bed automatically. Paying (from
  the confirmation page, browser callback or the webhook backstop if the
  browser never returns) flips the booking to `active`. A walk-in a staff
  member creates directly is trusted immediately and can be settled in cash,
  online, or marked to pay later — staff judgment, not a payment gate.
  *(If Razorpay isn't configured yet, online bookings fall back to "pay at
  check-in" exactly as before, so the site still works before payments are
  set up.)*
  If someone books online, never pays, and then simply walks in — the
  existing "Check-In" action in Admin → Bookings handles that too: it settles
  whatever payment method the resident actually pays with instead of leaving
  a duplicate pending charge behind.

## Project structure notes

- `app/Services/PaymentRecorder.php` is the single place that writes to the
  `payments` ledger and marks charges/dues as paid — online payments,
  webhook retries and cash entries all go through it, so nothing can be
  recorded twice for the same Razorpay payment.
- `app/Models/Setting.php` + `config/hostel.php` back the `setting()` helper
  used throughout the views for branding/contact/billing values.
- `app/Http/Middleware/EnsureUserIsStaff.php` (aliased `admin`) gates the
  whole `/admin` area to active staff accounts, and can be scoped to the
  owner only with `admin:admin` (used for Settings and Team).
- `App\Models\Booking::STATUSES` (`pending_payment`/`active`/`completed`/`cancelled`)
  is a plain validated string, not a DB enum, the same pattern used for
  `Due::TYPES` and `Request::TYPES` — adding a status later never needs
  another schema migration.
