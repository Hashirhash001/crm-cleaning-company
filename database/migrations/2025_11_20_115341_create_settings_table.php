<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, float, boolean
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default daily budget
        DB::table('settings')->insert([
            'key' => 'daily_budget_limit',
            'value' => '100000', // Default: 100,000
            'type' => 'float',
            'description' => 'Daily budget limit for job approvals',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
