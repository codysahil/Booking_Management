<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'vendor')) {
                $table->string('vendor')->nullable();
            }
            if (! Schema::hasColumn('expenses', 'payment_method')) {
                $table->string('payment_method')->default('cash');
            }
            if (! Schema::hasColumn('expenses', 'reference')) {
                $table->string('reference')->nullable();
            }
            if (! Schema::hasColumn('expenses', 'receipt_path')) {
                $table->string('receipt_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            foreach (['vendor', 'payment_method', 'reference', 'receipt_path'] as $col) {
                if (Schema::hasColumn('expenses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
