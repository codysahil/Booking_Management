<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A webhook and a browser callback can both try to record the same Razorpay
 * payment; PaymentRecorder::settle()/settleAdvance() already check for an
 * existing row first, but that's a check-then-act race under concurrency.
 * A unique constraint on razorpay_payment_id is the real guard — the loser
 * of the race gets a constraint violation instead of a duplicate row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['razorpay_payment_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('razorpay_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['razorpay_payment_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('razorpay_payment_id');
        });
    }
};
