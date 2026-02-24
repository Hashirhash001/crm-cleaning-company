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
            $table->dropUnique('leads_branch_phone_unique');

            // Also drop email unique index if one exists
            // $table->dropUnique('leads_branch_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unique(['branch_id', 'phone'], 'leads_branch_phone_unique');
        });
    }
};
