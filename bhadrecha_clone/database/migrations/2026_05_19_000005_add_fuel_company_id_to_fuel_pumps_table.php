<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_pumps', function (Blueprint $table) {
            if (Schema::hasColumn('fuel_pumps', 'brand')) {
                $table->dropColumn('brand');
            }
            if (!Schema::hasColumn('fuel_pumps', 'fuel_company_id')) {
                $table->foreignId('fuel_company_id')->nullable()->after('name')->constrained('fuel_companies')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fuel_pumps', function (Blueprint $table) {
            if (Schema::hasColumn('fuel_pumps', 'fuel_company_id')) {
                $table->dropForeign(['fuel_company_id']);
                $table->dropColumn('fuel_company_id');
            }
            if (!Schema::hasColumn('fuel_pumps', 'brand')) {
                $table->string('brand')->nullable()->after('name');
            }
        });
    }
};
