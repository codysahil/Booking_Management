<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A group booking (several beds checked out together) intentionally shares one
 * booking_reference across multiple `bookings` rows, so it must not be unique —
 * the unique index was silently breaking every multi-bed booking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['booking_reference']);
            $table->index('booking_reference');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['booking_reference']);
            $table->unique('booking_reference');
        });
    }
};
