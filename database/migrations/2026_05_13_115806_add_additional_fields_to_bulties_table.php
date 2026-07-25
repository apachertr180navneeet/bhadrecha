<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            if (!Schema::hasColumn('bulties', 'remark')) {
                $table->text('remark')->nullable()->after('pod_document_status');
                $table->decimal('bilty_commission', 10, 2)->default(0)->after('remark');
                $table->string('order_number')->nullable()->after('bilty_commission');
                $table->string('delivery_number')->nullable()->after('order_number');
                $table->string('from_no')->nullable()->after('delivery_number');
                $table->string('invoice_number')->nullable()->after('from_no');
                $table->date('invoice_date')->nullable()->after('invoice_number');
                $table->string('eway_bill_no')->nullable()->after('invoice_date');
                $table->date('generation_date')->nullable()->after('eway_bill_no');
                $table->date('expiry_date')->nullable()->after('generation_date');
                $table->decimal('advance_amount', 10, 2)->default(0)->after('expiry_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropColumn([
                'remark', 'bilty_commission',
                'order_number', 'delivery_number', 'from_no',
                'invoice_number', 'invoice_date',
                'eway_bill_no', 'generation_date', 'expiry_date',
                'advance_amount',
            ]);
        });
    }
};
