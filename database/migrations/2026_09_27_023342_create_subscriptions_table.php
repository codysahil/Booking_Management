<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One tenant's current billing state. Separate from Tenant.status (a hard
 * kill-switch the super admin controls) — this tracks whether the tenant may
 * currently write data, driven by Razorpay's subscription lifecycle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('razorpay_subscription_id')->nullable()->unique();
            $table->string('razorpay_customer_id')->nullable();
            // trialing|active|past_due|expired|cancelled|internal — see App\Models\Subscription.
            $table->string('status')->default('trialing');
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();
            // Set when a charge fails (subscription.pending): writes stay allowed
            // until this passes, giving Razorpay's own retries room to succeed.
            $table->timestamp('grace_ends_at')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->string('last_webhook_event')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
