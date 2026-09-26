<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A billing plan the super admin defines and offers to tenants — its price
 * here is the source of truth for what a hostel is charged; razorpay_plan_id
 * links it to the matching Plan created on Razorpay's side (via the
 * Subscriptions API) once one exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->string('billing_interval'); // monthly|yearly
            $table->string('razorpay_plan_id')->nullable()->unique();
            $table->unsignedInteger('max_branches')->nullable();
            $table->unsignedInteger('max_beds')->nullable();
            $table->unsignedInteger('max_staff')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
