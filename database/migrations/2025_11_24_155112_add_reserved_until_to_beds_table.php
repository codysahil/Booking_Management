<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Disable transaction for this migration to avoid "current transaction is aborted" errors.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('beds') && !Schema::hasColumn('beds', 'reserved_until')) {
            Schema::table('beds', function (Blueprint $table) {
                $table->timestamp('reserved_until')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            $table->dropColumn('reserved_until');
        });
    }
};
