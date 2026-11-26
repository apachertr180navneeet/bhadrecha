<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'gst_type')) {
                $table->string('gst_type')->default('CGST_SGST')->after('gst_master_id');
            }
            if (!Schema::hasColumn('invoices', 'cgst_amount')) {
                $table->decimal('cgst_amount', 12, 2)->default(0.00)->after('gst_type');
            }
            if (!Schema::hasColumn('invoices', 'sgst_amount')) {
                $table->decimal('sgst_amount', 12, 2)->default(0.00)->after('cgst_amount');
            }
            if (!Schema::hasColumn('invoices', 'igst_amount')) {
                $table->decimal('igst_amount', 12, 2)->default(0.00)->after('sgst_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['gst_type', 'cgst_amount', 'sgst_amount', 'igst_amount']);
        });
    }
};
