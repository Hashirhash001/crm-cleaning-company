<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop existing normal indexes
            $table->dropIndex('idx_leads_email');
            $table->dropIndex('idx_leads_phone');

            // Add branch-wise unique constraints
            $table->unique(['branch_id', 'email'], 'leads_branch_email_unique');
            $table->unique(['branch_id', 'phone'], 'leads_branch_phone_unique');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique('leads_branch_email_unique');
            $table->dropUnique('leads_branch_phone_unique');

            // Restore the previous non-unique indexes
            $table->index('email', 'idx_leads_email');
            $table->index('phone', 'idx_leads_phone');
        });
    }
};

