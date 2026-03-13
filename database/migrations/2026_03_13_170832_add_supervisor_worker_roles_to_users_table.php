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
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'super_admin',
            'lead_manager',
            'field_staff',
            'telecallers',
            'supervisor',
            'worker'
        ) DEFAULT 'telecallers'");
    }

    public function down(): void
    {
        // Note: only safe to revert if no users have supervisor/worker roles
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'super_admin',
            'lead_manager',
            'field_staff',
            'telecallers'
        ) DEFAULT 'telecallers'");
    }

};
