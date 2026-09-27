<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A booking's advance_paid doubles as the security deposit in this app's
 * convention, but there was previously no record of what happened to it at
 * move-out — vacating a customer just freed the bed with no reconciliation
 * at all. These fields capture that decision once, at vacate time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('deposit_deduction_amount', 10, 2)->nullable()->after('advance_paid');
            $table->text('deposit_deduction_reason')->nullable()->after('deposit_deduction_amount');
            $table->decimal('deposit_refund_amount', 10, 2)->nullable()->after('deposit_deduction_reason');
            $table->timestamp('settled_at')->nullable()->after('deposit_refund_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['deposit_deduction_amount', 'deposit_deduction_reason', 'deposit_refund_amount', 'settled_at']);
        });
    }
};
