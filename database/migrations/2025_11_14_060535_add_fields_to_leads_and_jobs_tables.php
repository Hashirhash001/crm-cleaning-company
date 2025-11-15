<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('lead_code')->unique()->after('id'); // LEAD001
            $table->unsignedBigInteger('service_id')->nullable()->after('lead_source_id');

            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->string('job_code')->unique()->after('id'); // JOB001
            $table->unsignedBigInteger('customer_id')->nullable()->after('lead_id');
            $table->unsignedBigInteger('service_id')->nullable()->after('customer_id');
            $table->text('customer_instructions')->nullable()->after('description');

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['lead_code', 'service_id']);
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['customer_id', 'service_id']);
            $table->dropColumn(['job_code', 'customer_id', 'service_id', 'customer_instructions']);
        });
    }
};
