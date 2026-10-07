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
        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('full_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();

            // Position & Department
            $table->unsignedBigInteger('recruitment_position_id');
            $table->unsignedBigInteger('recruitment_department_id');
            $table->string('current_company')->nullable();

            // Interview Details
            $table->date('interview_date')->nullable();
            $table->time('interview_time')->nullable();
            $table->unsignedBigInteger('interviewer_id')->nullable();
            $table->enum('interview_status', [
                'Scheduled', 'Completed', 'Pending', 'Cancelled', 'Rescheduled'
            ])->default('Pending');

            // Interview Result
            $table->enum('interview_result', [
                'Selected', 'Rejected', 'Hold', 'Pending'
            ])->default('Pending');

            // Joining Details
            $table->enum('joining_status', [
                'Joined', 'Pending', 'Hold', 'Not Joined', 'Cancelled'
            ])->default('Pending');
            $table->date('expected_join_date')->nullable();
            $table->date('follow_up_date')->nullable();

            // Decision & Notes
            $table->enum('decision_reason', [
                'No Response', 'Not Interested', 'Rejected', 'Selected', 'Hold', 'By Company', 'Other'
            ])->nullable();
            $table->text('remarks')->nullable();
            $table->enum('reference', [
                'WhatsApp', 'Friends', 'Employee Referral', 'Website', 'Indeed', 'LinkedIn', 'Other'
            ])->nullable();

            // Tracking
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('recruitment_position_id')
                  ->references('id')
                  ->on('recruitment_positions')
                  ->onDelete('restrict');

            $table->foreign('recruitment_department_id')
                  ->references('id')
                  ->on('recruitment_departments')
                  ->onDelete('restrict');

            $table->foreign('interviewer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Indexes for search/filter performance
            $table->index('interview_status');
            $table->index('interview_result');
            $table->index('joining_status');
            $table->index('interview_date');
            $table->index('follow_up_date');
            $table->index('reference');
            $table->index('decision_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidates');
    }
};
