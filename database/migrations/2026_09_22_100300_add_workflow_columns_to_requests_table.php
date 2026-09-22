<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (! Schema::hasColumn('requests', 'preferred_date')) {
                // Move-out date for vacation notices, preferred visit date for service requests.
                $table->date('preferred_date')->nullable();
            }
            if (! Schema::hasColumn('requests', 'admin_response')) {
                $table->text('admin_response')->nullable();
            }
            if (! Schema::hasColumn('requests', 'handled_by')) {
                $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('requests', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (Schema::hasColumn('requests', 'handled_by')) {
                $table->dropConstrainedForeignId('handled_by');
            }
            foreach (['preferred_date', 'admin_response', 'resolved_at'] as $col) {
                if (Schema::hasColumn('requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
