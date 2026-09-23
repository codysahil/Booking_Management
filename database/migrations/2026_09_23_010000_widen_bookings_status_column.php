<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * bookings.status was a DB-level enum (active/completed/cancelled). A new
 * "pending_payment" status (an online booking that hasn't been paid for yet)
 * needs to be added — rather than keep widening a DB enum every time a status
 * is added, switch to a plain string validated in the app (the same approach
 * already used for Due::TYPES and Request::TYPES) so this never needs another
 * migration like this again.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Postgres implements Laravel's enum() as a CHECK constraint; a plain
            // ->change() can't alter the type while that constraint references it.
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check');
            DB::statement('ALTER TABLE bookings ALTER COLUMN status TYPE VARCHAR(255)');
            DB::statement("ALTER TABLE bookings ALTER COLUMN status SET DEFAULT 'active'");

            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE bookings SET status = 'cancelled' WHERE status NOT IN ('active', 'completed', 'cancelled')");
            DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status IN ('active', 'completed', 'cancelled'))");

            return;
        }

        DB::table('bookings')->whereNotIn('status', ['active', 'completed', 'cancelled'])->update(['status' => 'cancelled']);

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active')->change();
        });
    }
};
