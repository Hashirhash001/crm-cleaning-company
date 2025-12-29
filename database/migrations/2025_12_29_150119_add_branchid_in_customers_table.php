<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            // Drop old unique constraints if they exist
            $table->dropUnique(['email']);
            $table->dropUnique(['phone']);

            // Add composite unique constraints: unique per branch
            $table->unique(['email', 'branch_id'], 'customers_email_branch_unique');
            $table->unique(['phone', 'branch_id'], 'customers_phone_branch_unique');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_email_branch_unique');
            $table->dropUnique('customers_phone_branch_unique');

            // Restore old unique constraints
            $table->unique('email');
            $table->unique('phone');
        });
    }
};
