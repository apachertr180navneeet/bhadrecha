<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'custom_place_of_supply')) {
                $table->string('custom_place_of_supply')->nullable()->after('custom_hsn_code');
            }
            if (!Schema::hasColumn('invoices', 'custom_district')) {
                $table->string('custom_district')->nullable()->after('custom_place_of_supply');
            }
            if (!Schema::hasColumn('invoices', 'custom_state')) {
                $table->string('custom_state')->nullable()->after('custom_district');
            }
            if (!Schema::hasColumn('invoices', 'custom_state_code')) {
                $table->string('custom_state_code')->nullable()->after('custom_state');
            }
            if (!Schema::hasColumn('invoices', 'custom_gstn')) {
                $table->string('custom_gstn')->nullable()->after('custom_state_code');
            }
            if (!Schema::hasColumn('invoices', 'custom_pan_no')) {
                $table->string('custom_pan_no')->nullable()->after('custom_gstn');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $columns = ['custom_place_of_supply', 'custom_district', 'custom_state', 'custom_state_code', 'custom_gstn', 'custom_pan_no'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
