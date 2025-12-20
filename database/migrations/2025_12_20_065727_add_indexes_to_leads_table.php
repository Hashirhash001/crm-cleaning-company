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
        Schema::table('leads', function (Blueprint $table) {
            // ============================================
            // CRITICAL INDEXES (Add these first)
            // ============================================

            // 1. Status - Most frequently filtered
            $table->index('status', 'idx_leads_status');

            // 2. Created At - Default sorting and date filters
            $table->index('created_at', 'idx_leads_created_at');

            // 3. Composite index for status + created_at (MOST IMPORTANT!)
            // This optimizes: WHERE status = 'X' ORDER BY created_at DESC
            $table->index(['status', 'created_at'], 'idx_leads_status_created_at');

            // ============================================
            // HIGH PRIORITY INDEXES
            // ============================================

            // 4. Phone - Search and duplicate checking
            $table->index('phone', 'idx_leads_phone');

            // ============================================
            // MEDIUM PRIORITY INDEXES
            // ============================================

            // 5. Email - Search and duplicate checking
            $table->index('email', 'idx_leads_email');

            // 6. Name - Search functionality
            $table->index('name', 'idx_leads_name');

            // 7. Deleted At - For soft delete queries (if using SoftDeletes)
            $table->index('deleted_at', 'idx_leads_deleted_at');

            // ============================================
            // OPTIONAL COMPOSITE INDEXES (Advanced optimization)
            // ============================================

            // 8. Branch + Status - For branch-filtered views
            // Used when: WHERE branch_id = X AND status = Y
            $table->index(['branch_id', 'status'], 'idx_leads_branch_status');

            // 9. Assigned To + Status - For telecaller views
            // Used when: WHERE assigned_to = X AND status = Y
            $table->index(['assigned_to', 'status'], 'idx_leads_assigned_status');

            // 10. Service Type + Status - For service filtering
            // Used when: WHERE service_type = 'cleaning' AND status = 'pending'
            $table->index(['service_type', 'status'], 'idx_leads_service_type_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop all indexes
            $table->dropIndex('idx_leads_status');
            $table->dropIndex('idx_leads_created_at');
            $table->dropIndex('idx_leads_status_created_at');
            $table->dropIndex('idx_leads_phone');
            $table->dropIndex('idx_leads_email');
            $table->dropIndex('idx_leads_name');
            $table->dropIndex('idx_leads_deleted_at');
            $table->dropIndex('idx_leads_branch_status');
            $table->dropIndex('idx_leads_assigned_status');
            $table->dropIndex('idx_leads_service_type_status');
        });
    }
};
