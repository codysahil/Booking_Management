<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Disable transaction for this migration to avoid "current transaction is aborted" errors.
     */
    public $withinTransaction = false;

    public function up(): void
    {
        if (!Schema::hasTable('monthly_charges')) {
            Schema::create('monthly_charges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->foreignId('booking_id')->constrained()->onDelete('cascade');
                $table->string('month_year'); // Format: 2025-11
                $table->decimal('rent_amount', 10, 2);
                $table->decimal('eb_amount', 10, 2)->default(0);
                $table->decimal('other_charges', 10, 2)->default(0);
                $table->text('other_charges_description')->nullable();
                $table->decimal('total_amount', 10, 2);
                $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
                $table->date('due_date');
                $table->date('paid_date')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();
                $table->timestamps();

                $table->unique(['customer_id', 'month_year']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_charges');
    }
};
