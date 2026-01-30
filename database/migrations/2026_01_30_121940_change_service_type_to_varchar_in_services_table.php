<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Change from enum to string (varchar)
            $table->string('service_type', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Revert back to enum
            $table->enum('service_type', ['cleaning', 'pest_control', 'other'])->change();
        });
    }
};
