<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A tenant is one hostel/PG business subscribing to this platform. Every
 * hostel-owned record (staff, customers, branches, settings, etc.) belongs
 * to exactly one tenant; nothing is ever shared across tenants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // A hard kill-switch the super admin controls (can this account even log in) —
            // deliberately separate from subscription billing status (can it write data).
            $table->string('status')->default('active');
            $table->string('owner_name')->nullable();
            $table->string('owner_email')->nullable();
            $table->string('owner_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
