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
        if (Schema::hasTable('bulties')) {
            Schema::table('bulties', function (Blueprint $table) {
                if (!Schema::hasColumn('bulties', 'delivery_date')) {
                    $table->date('delivery_date')->nullable()->after('delivery_number');
                }
                if (!Schema::hasColumn('bulties', 'delivery_time')) {
                    $table->string('delivery_time')->nullable()->after('delivery_date');
                }
            });
        }

        if (Schema::hasTable('bulty_details')) {
            Schema::table('bulty_details', function (Blueprint $table) {
                if (!Schema::hasColumn('bulty_details', 'delivery_date')) {
                    $table->date('delivery_date')->nullable()->after('ul_date');
                }
                if (!Schema::hasColumn('bulty_details', 'delivery_time')) {
                    $table->string('delivery_time')->nullable()->after('delivery_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bulties')) {
            Schema::table('bulties', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('bulties', 'delivery_date')) $columns[] = 'delivery_date';
                if (Schema::hasColumn('bulties', 'delivery_time')) $columns[] = 'delivery_time';
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('bulty_details')) {
            Schema::table('bulty_details', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('bulty_details', 'delivery_date')) $columns[] = 'delivery_date';
                if (Schema::hasColumn('bulty_details', 'delivery_time')) $columns[] = 'delivery_time';
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
