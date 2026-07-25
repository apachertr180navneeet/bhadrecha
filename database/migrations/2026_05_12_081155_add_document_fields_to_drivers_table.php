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
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('license_front')->nullable()->after('status');
            $table->string('license_back')->nullable()->after('license_front');
            $table->string('aadhar_front')->nullable()->after('license_back');
            $table->string('aadhar_back')->nullable()->after('aadhar_front');
            $table->string('pan_front')->nullable()->after('aadhar_back');
            $table->string('pan_back')->nullable()->after('pan_front');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['license_front', 'license_back', 'aadhar_front', 'aadhar_back', 'pan_front', 'pan_back']);
        });
    }
};
