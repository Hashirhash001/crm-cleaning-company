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
        Schema::table('job_service', function (Blueprint $table) {
            // Add quantity column with default value of 1
            $table->integer('quantity')->default(1)->after('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_service', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
