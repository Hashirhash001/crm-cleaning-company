<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lead_imports', function (Blueprint $table) {
            // Change failed_rows_data to JSON
            $table->json('failed_rows_data')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('lead_imports', function (Blueprint $table) {
            $table->longText('failed_rows_data')->nullable()->change();
        });
    }
};
