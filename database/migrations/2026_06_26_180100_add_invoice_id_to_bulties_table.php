<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bulties') && !Schema::hasColumn('bulties', 'invoice_id')) {
            Schema::table('bulties', function (Blueprint $table) {
                $table->unsignedBigInteger('invoice_id')->nullable()->after('bill_status');
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bulties') && Schema::hasColumn('bulties', 'invoice_id')) {
            Schema::table('bulties', function (Blueprint $table) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            });
        }
    }
};
