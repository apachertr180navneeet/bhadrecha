<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_fuel_details', function (Blueprint $table) {
            $table->date('date')->nullable()->after('builty_id');
            $table->foreignId('fuel_company_id')->nullable()->constrained('fuel_companies')->after('date');
            $table->decimal('rate', 10, 2)->default(0)->after('quantity');
            $table->decimal('km', 10, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('trip_fuel_details', function (Blueprint $table) {
            $table->dropColumn(['date', 'fuel_company_id', 'rate', 'km']);
        });
    }
};
