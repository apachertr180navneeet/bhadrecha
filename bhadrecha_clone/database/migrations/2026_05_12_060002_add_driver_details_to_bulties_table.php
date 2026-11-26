<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            // Missing basic fields
            if (!Schema::hasColumn('bulties', 'from_city')) $table->unsignedBigInteger('from_city')->nullable()->after('lr_date');
            if (!Schema::hasColumn('bulties', 'to_city')) $table->unsignedBigInteger('to_city')->nullable()->after('from_city');
            
            // Vehicle details
            if (!Schema::hasColumn('bulties', 'vehicle_id')) $table->unsignedBigInteger('vehicle_id')->nullable()->after('payment_type');
            if (!Schema::hasColumn('bulties', 'vehicle_number')) $table->string('vehicle_number')->nullable()->after('vehicle_id');
            if (!Schema::hasColumn('bulties', 'vehicle_type')) $table->string('vehicle_type')->nullable()->after('vehicle_number');
            if (!Schema::hasColumn('bulties', 'make_model')) $table->string('make_model')->nullable()->after('vehicle_type');
            if (!Schema::hasColumn('bulties', 'capacity_tons')) $table->decimal('capacity_tons', 10, 2)->nullable()->after('make_model');
            if (!Schema::hasColumn('bulties', 'owner_name')) $table->string('owner_name')->nullable()->after('capacity_tons');
            if (!Schema::hasColumn('bulties', 'owner_phone')) $table->string('owner_phone')->nullable()->after('owner_name');
            
            // Driver details (Basic + Full)
            if (!Schema::hasColumn('bulties', 'driver_name')) $table->string('driver_name')->nullable()->after('owner_phone');
            if (!Schema::hasColumn('bulties', 'driver_mobile')) $table->string('driver_mobile')->nullable()->after('driver_name');
            if (!Schema::hasColumn('bulties', 'driver_license_no')) $table->string('driver_license_no')->nullable()->after('driver_mobile');
            if (!Schema::hasColumn('bulties', 'driver_license_expiry')) $table->date('driver_license_expiry')->nullable()->after('driver_license_no');
            if (!Schema::hasColumn('bulties', 'driver_address')) $table->string('driver_address')->nullable()->after('driver_license_expiry');

            // Expiry details
            if (!Schema::hasColumn('bulties', 'insurance_expiry')) $table->date('insurance_expiry')->nullable()->after('driver_address');
            if (!Schema::hasColumn('bulties', 'fitness_expiry')) $table->date('fitness_expiry')->nullable()->after('insurance_expiry');
            if (!Schema::hasColumn('bulties', 'permit_expiry')) $table->date('permit_expiry')->nullable()->after('fitness_expiry');

            // GST Master
            if (!Schema::hasColumn('bulties', 'gst_master_id')) $table->unsignedBigInteger('gst_master_id')->nullable()->after('gst_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropColumn([
                'from_city', 'to_city', 'vehicle_id', 'vehicle_number', 'vehicle_type', 'make_model', 
                'capacity_tons', 'owner_name', 'owner_phone', 'driver_name', 'driver_mobile', 
                'driver_license_no', 'driver_license_expiry', 'driver_address',
                'insurance_expiry', 'fitness_expiry', 'permit_expiry', 'gst_master_id'
            ]);
        });
    }
};
