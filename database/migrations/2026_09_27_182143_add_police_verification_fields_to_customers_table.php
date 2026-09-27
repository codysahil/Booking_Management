<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PG/hostel operators in India are commonly required to register new
 * tenants with the local police station. There was previously nowhere to
 * record an ID's type/number (only a scanned file) or track whether that
 * registration had been submitted/verified for a given resident.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('id_proof_type')->nullable()->after('id_proof_path');
            $table->string('id_proof_number')->nullable()->after('id_proof_type');
            $table->string('police_verification_status')->default('pending')->after('id_proof_number');
            $table->timestamp('police_verification_submitted_at')->nullable()->after('police_verification_status');
            $table->timestamp('police_verification_verified_at')->nullable()->after('police_verification_submitted_at');
            $table->text('police_verification_notes')->nullable()->after('police_verification_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'id_proof_type',
                'id_proof_number',
                'police_verification_status',
                'police_verification_submitted_at',
                'police_verification_verified_at',
                'police_verification_notes',
            ]);
        });
    }
};
