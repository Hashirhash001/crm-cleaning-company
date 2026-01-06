<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For MySQL - modify enum to include 'approved'
        DB::statement("ALTER TABLE jobs MODIFY COLUMN status ENUM('pending', 'confirmed', 'approved', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE jobs MODIFY COLUMN status ENUM('pending', 'confirmed', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
