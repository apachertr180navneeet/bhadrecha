<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_fuel_details', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)->default(0)->change();
            $table->decimal('rate', 15, 2)->default(0)->change();
            $table->decimal('amount', 15, 2)->default(0)->change();
            $table->decimal('km', 15, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('trip_fuel_details', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(0)->change();
            $table->decimal('rate', 10, 2)->default(0)->change();
            $table->decimal('amount', 10, 2)->default(0)->change();
            $table->decimal('km', 10, 2)->default(0)->change();
        });
    }
};
