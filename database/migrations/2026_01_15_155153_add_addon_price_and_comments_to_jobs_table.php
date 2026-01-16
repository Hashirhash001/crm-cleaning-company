<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->decimal('addon_price', 10, 2)->nullable()->after('amount_paid');
            $table->text('addon_price_comments')->nullable()->after('addon_price');
        });
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['addon_price', 'comments']);
        });
    }
};
