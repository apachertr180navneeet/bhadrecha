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
        Schema::table('bill_formats', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_formats', 'template_type')) {
                $table->string('template_type')->default('standard')->after('format_name');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'template_type')) {
                $table->string('template_type')->default('standard')->after('amount_in_words');
            }
        });

        Schema::table('bulty_details', function (Blueprint $table) {
            if (!Schema::hasColumn('bulty_details', 'damage_qty')) {
                $table->decimal('damage_qty', 10, 2)->default(0)->after('short_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_formats', function (Blueprint $table) {
            $table->dropColumn('template_type');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('template_type');
        });

        Schema::table('bulty_details', function (Blueprint $table) {
            $table->dropColumn('damage_qty');
        });
    }
};
