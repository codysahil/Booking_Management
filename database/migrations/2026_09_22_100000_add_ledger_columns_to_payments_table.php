<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turns `payments` into a proper ledger: every collected amount (online or cash)
 * gets one row with a receipt number, the items it settled and who recorded it.
 * Column checks keep this safe on databases that were patched by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'monthly_charge_id')) {
                $table->foreignId('monthly_charge_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'due_id')) {
                $table->foreignId('due_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->index();
            }
            if (! Schema::hasColumn('payments', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->index();
            }
            if (! Schema::hasColumn('payments', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->unique();
            }
            if (! Schema::hasColumn('payments', 'items')) {
                $table->json('items')->nullable();
            }
            if (! Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('payments', 'recorded_by')) {
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            foreach (['monthly_charge_id', 'due_id', 'recorded_by'] as $fk) {
                if (Schema::hasColumn('payments', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            foreach (['razorpay_order_id', 'razorpay_payment_id', 'receipt_number', 'items', 'notes'] as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
