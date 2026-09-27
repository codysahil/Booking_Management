<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A prospective resident's self-submitted details from a hostel's public
 * intake link (see routes/web.php's "register" group and
 * App\Http\Controllers\Public\IntakeController), before an admin has ever
 * seen them. An admin reviews these and either converts one into a real
 * Customer + Booking at check-in time, or rejects it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->date('dob');
            $table->text('address');
            $table->string('guardian_phone', 20);
            $table->text('work_details')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('id_proof_path')->nullable();
            $table->date('preferred_move_in_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_applications');
    }
};
