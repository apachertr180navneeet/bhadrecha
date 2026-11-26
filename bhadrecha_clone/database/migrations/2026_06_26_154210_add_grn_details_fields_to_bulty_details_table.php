<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulty_details', function (Blueprint $table) {
            if (!Schema::hasColumn('bulty_details', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('bulty_id')->constrained('suppliers')->nullOnDelete();
            }
            if (!Schema::hasColumn('bulty_details', 'challan_date')) {
                $table->date('challan_date')->nullable()->after('challan_no');
            }
            if (!Schema::hasColumn('bulty_details', 'transporter_code')) {
                $table->string('transporter_code')->nullable()->after('challan_date');
            }
            if (!Schema::hasColumn('bulty_details', 'transporter_name')) {
                $table->string('transporter_name')->nullable()->after('transporter_code');
            }
            if (!Schema::hasColumn('bulty_details', 'po_item')) {
                $table->string('po_item')->nullable()->after('po_no');
            }
            if (!Schema::hasColumn('bulty_details', 'challan_qty')) {
                $table->decimal('challan_qty', 12, 3)->default(0)->after('recd_qty');
            }
            if (!Schema::hasColumn('bulty_details', 'final_wgt')) {
                $table->decimal('final_wgt', 12, 3)->default(0)->after('challan_qty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulty_details', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'challan_date', 'transporter_code', 'transporter_name', 'po_item', 'challan_qty', 'final_wgt']);
        });
    }
};
