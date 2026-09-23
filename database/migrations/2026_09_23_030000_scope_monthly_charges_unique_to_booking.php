<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * monthly_charges had a unique(customer_id, month_year) constraint, meaning the
 * database itself only ever allowed one charge per customer per month — a customer
 * holding two beds (two Booking rows) could never be billed for both, no matter what
 * the application-level dedupe check looked at. The correct scope is per booking:
 * one charge per booking per month, so a customer with multiple bookings gets billed
 * for each of them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_charges', function (Blueprint $table) {
            $table->dropUnique(['customer_id', 'month_year']);
        });

        Schema::table('monthly_charges', function (Blueprint $table) {
            $table->unique(['booking_id', 'month_year']);
        });
    }

    public function down(): void
    {
        Schema::table('monthly_charges', function (Blueprint $table) {
            $table->dropUnique(['booking_id', 'month_year']);
        });

        Schema::table('monthly_charges', function (Blueprint $table) {
            $table->unique(['customer_id', 'month_year']);
        });
    }
};
