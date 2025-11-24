<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN, so we'll use a different approach
        // For SQLite, we need to recreate the table or just skip the enum change
        // Since SQLite doesn't have enum types, we'll just ensure the column exists
        
        // Check if we're using MySQL
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'checked_in', 'active', 'completed', 'cancelled') DEFAULT 'pending'");
        }
        // For SQLite, the status column already exists as TEXT, which accepts any value
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum (only for MySQL)
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('active', 'completed', 'cancelled') DEFAULT 'active'");
        }
    }
};
