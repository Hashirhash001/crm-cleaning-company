<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->after('description');
            $table->timestamp('amount_updated_at')->nullable()->after('amount');
            $table->unsignedBigInteger('amount_updated_by')->nullable()->after('amount_updated_at');

            $table->foreign('amount_updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['amount_updated_by']);
            $table->dropColumn(['amount', 'amount_updated_at', 'amount_updated_by']);
        });
    }
};
