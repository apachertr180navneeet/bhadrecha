<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_advance_details', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('advance_amount');
        });
    }

    public function down(): void
    {
        Schema::table('trip_advance_details', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
