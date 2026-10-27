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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'e_invoice_no')) {
                $table->string('e_invoice_no')->nullable()->after('bill_number');
            }
            if (!Schema::hasColumn('invoices', 'ee_invoice_no')) {
                $table->string('ee_invoice_no')->nullable()->after('e_invoice_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'e_invoice_no')) {
                $table->dropColumn('e_invoice_no');
            }
            if (Schema::hasColumn('invoices', 'ee_invoice_no')) {
                $table->dropColumn('ee_invoice_no');
            }
        });
    }
};
