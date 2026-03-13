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
        Schema::create('job_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('feedback')->nullable();
            $table->enum('rated_by_type', ['customer', 'admin', 'staff'])->default('admin');
            $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_ratings');
    }
};
