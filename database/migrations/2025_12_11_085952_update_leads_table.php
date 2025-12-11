<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Add service type selection
            $table->enum('service_type', ['cleaning', 'pest_control'])->nullable()->after('description');

            // Make service_id nullable since we're moving to multi-select
            // Keep it for backward compatibility
            $table->unsignedBigInteger('service_id')->nullable()->change();

            // Add other new fields
            $table->string('phone_alternative')->nullable()->after('phone');
            $table->string('address')->nullable()->after('email');
            $table->string('district')->nullable()->after('address');
            $table->enum('property_type', ['commercial', 'residential'])->nullable()->after('service_type');
            $table->integer('sqft')->nullable()->after('property_type');
            $table->decimal('advance_payment', 10, 2)->nullable()->after('amount');
            $table->decimal('advance_paid_amount', 10, 2)->default(0)->after('advance_payment');
            $table->enum('payment_mode', ['cash', 'upi', 'card', 'bank_transfer', 'neft'])->nullable()->after('advance_paid_amount');
        });

        // Update status enum
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->enum('status', [
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
            ])->default('pending')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'service_type',
                'phone_alternative',
                'address',
                'district',
                'property_type',
                'sqft',
                'advance_payment',
                'advance_paid_amount',
                'payment_mode'
            ]);
        });
    }
};
