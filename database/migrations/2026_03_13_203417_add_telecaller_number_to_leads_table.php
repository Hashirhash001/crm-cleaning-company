<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('telecaller_number', 20)->nullable()->after('phone_alternative');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('telecaller_number');
        });
    }
};
