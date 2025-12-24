<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityToLeadServiceTable extends Migration
{
    public function up()
    {
        Schema::table('lead_service', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('service_id');
        });
    }

    public function down()
    {
        Schema::table('lead_service', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
}
