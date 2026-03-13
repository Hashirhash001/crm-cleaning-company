<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // registered staff
            $table->string('temp_name')->nullable();   // temporary worker name
            $table->string('temp_phone')->nullable();  // temporary worker phone
            $table->enum('role', ['supervisor', 'worker']);
            $table->enum('staff_type', ['registered', 'temporary']);
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_staff');
    }
};
