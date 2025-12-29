<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE `leads` MODIFY COLUMN `status` ENUM(
            'pending',
            'site_visit',
            'not_accepting_tc',
            'they_will_confirm',
            'date_issue',
            'rate_issue',
            'service_not_provided',
            'just_enquiry',
            'immediate_service',
            'no_response',
            'location_not_available',
            'night_work_demanded',
            'customisation',
            'confirmed',
            'approved',
            'rejected'
        ) NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `leads` MODIFY COLUMN `status` ENUM(
            'pending',
            'site_visit',
            'not_accepting_tc',
            'they_will_confirm',
            'date_issue',
            'rate_issue',
            'service_not_provided',
            'just_enquiry',
            'immediate_service',
            'no_response',
            'location_not_available',
            'night_work_demanded',
            'customisation',
            'approved',
            'rejected'
        ) NOT NULL DEFAULT 'pending'");
    }
};
