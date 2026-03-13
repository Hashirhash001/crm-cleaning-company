<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new status to the jobs enum
        DB::statement("ALTER TABLE jobs MODIFY COLUMN status ENUM(
            'pending','confirmed','approved','assigned','in_progress',
            'completed','postponed','work_on_hold','cancelled',
            'staff_pending_approval'
        ) DEFAULT 'pending'");

        Schema::table('job_staff', function (Blueprint $table) {
            // ✅ Drop foreign key FIRST, then drop the columns
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approval_notes']);
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE jobs MODIFY COLUMN status ENUM(
            'pending','confirmed','approved','assigned','in_progress',
            'completed','postponed','work_on_hold','cancelled'
        ) DEFAULT 'pending'");

        Schema::table('job_staff', function (Blueprint $table) {
            $table->enum('approval_status', ['pending','approved','rejected'])->default('pending')->after('notes');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });
    }

};
