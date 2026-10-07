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
        Schema::table('recruitment_candidates', function (Blueprint $table) {
            // Drop foreign key and interviewer_id column if present
            if (Schema::hasColumn('recruitment_candidates', 'interviewer_id')) {
                $table->dropForeign(['interviewer_id']);
                $table->dropColumn('interviewer_id');
            }

            // Add interviewer as free-text string field
            if (!Schema::hasColumn('recruitment_candidates', 'interviewer')) {
                $table->string('interviewer')->nullable()->after('interview_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_candidates', function (Blueprint $table) {
            if (Schema::hasColumn('recruitment_candidates', 'interviewer')) {
                $table->dropColumn('interviewer');
            }

            $table->unsignedBigInteger('interviewer_id')->nullable()->after('interview_time');
            $table->foreign('interviewer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
