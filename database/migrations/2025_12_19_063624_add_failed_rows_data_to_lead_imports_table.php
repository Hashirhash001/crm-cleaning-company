<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lead_imports', function (Blueprint $table) {
            $table->longText('failed_rows_data')->nullable()->after('errors');
        });
    }

    public function down()
    {
        Schema::table('lead_imports', function (Blueprint $table) {
            $table->dropColumn('failed_rows_data');
        });
    }
};
