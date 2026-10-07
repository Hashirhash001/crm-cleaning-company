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
        Schema::table('recruitment_candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment_candidates', 'place')) {
                $table->string('place')->nullable()->after('email');
            }
            if (!Schema::hasColumn('recruitment_candidates', 'district')) {
                $table->string('district')->nullable()->after('place');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_candidates', function (Blueprint $table) {
            $table->dropColumn(['place', 'district']);
        });
    }
};
