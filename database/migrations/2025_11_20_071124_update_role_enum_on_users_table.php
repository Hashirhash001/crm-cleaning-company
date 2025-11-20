<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add telecallers WITHOUT removing reporting_user yet
        DB::statement("
            ALTER TABLE `users`
            MODIFY `role` ENUM('super_admin', 'lead_manager', 'field_staff', 'reporting_user', 'telecallers')
            NOT NULL DEFAULT 'reporting_user'
        ");

        // 2) Now convert old values to new one
        DB::table('users')
            ->where('role', 'reporting_user')
            ->update(['role' => 'telecallers']);

        // 3) Finally, remove reporting_user from enum and set new default
        DB::statement("
            ALTER TABLE `users`
            MODIFY `role` ENUM('super_admin', 'lead_manager', 'field_staff', 'telecallers')
            NOT NULL DEFAULT 'telecallers'
        ");
    }

    public function down(): void
    {
        // Reverse process

        // 1) Re‑add reporting_user to enum definition
        DB::statement("
            ALTER TABLE `users`
            MODIFY `role` ENUM('super_admin', 'lead_manager', 'field_staff', 'reporting_user', 'telecallers')
            NOT NULL DEFAULT 'telecallers'
        ");

        // 2) Convert telecallers back to reporting_user
        DB::table('users')
            ->where('role', 'telecallers')
            ->update(['role' => 'reporting_user']);

        // 3) Remove telecallers and restore original default
        DB::statement("
            ALTER TABLE `users`
            MODIFY `role` ENUM('super_admin', 'lead_manager', 'field_staff', 'reporting_user')
            NOT NULL DEFAULT 'reporting_user'
        ");
    }
};
