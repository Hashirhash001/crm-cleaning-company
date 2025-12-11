<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->enum('callback_time_preference', [
                'morning',
                'afternoon',
                'evening',
                'anytime'
            ])->nullable()->after('followup_time');
        });
    }

    public function down(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dropColumn('callback_time_preference');
        });
    }
};
