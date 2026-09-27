<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recorded explicitly at charge-creation time rather than derived later from
 * the booking's check-in date: a derived guess ("this charge's month equals
 * the booking's check-in month") would wrongly relabel old, already-full-rent
 * charges as prorated once enough time passes for their month to coincide
 * with a booking created back then. Existing rows default to false, which is
 * correct — they were all generated as full months before this feature existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_charges', function (Blueprint $table) {
            $table->boolean('is_prorated')->default(false)->after('rent_amount');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_charges', function (Blueprint $table) {
            $table->dropColumn('is_prorated');
        });
    }
};
