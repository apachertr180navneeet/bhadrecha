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
        Schema::table('trip_adblue_details', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_adblue_details', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
