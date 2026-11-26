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
                if (!Schema::hasColumn('bulties', 'delivered_at')) {
                    $table->dateTime('delivered_at')->nullable()->after('delivery_time');
                }
            });
        }

        if (Schema::hasTable('bulty_details')) {
            Schema::table('bulty_details', function (Blueprint $table) {
                if (!Schema::hasColumn('bulty_details', 'delivered_at')) {
                    $table->dateTime('delivered_at')->nullable()->after('delivery_time');
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
                if (Schema::hasColumn('bulties', 'delivered_at')) {
                    $table->dropColumn('delivered_at');
                }
            });
        }

        if (Schema::hasTable('bulty_details')) {
            Schema::table('bulty_details', function (Blueprint $table) {
                if (Schema::hasColumn('bulty_details', 'delivered_at')) {
                    $table->dropColumn('delivered_at');
                }
            });
        }
    }
};
