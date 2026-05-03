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
            $table->string('service_type')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('service_type')->nullable(false)->change();
        });
    }
};
