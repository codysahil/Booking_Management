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
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('category'); // Food, Salary, Maintenance, Custom
                $table->decimal('amount', 10, 2);
                $table->date('date');
                $table->text('description')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin ID
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
