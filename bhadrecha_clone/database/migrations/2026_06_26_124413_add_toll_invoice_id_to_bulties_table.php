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
        if (Schema::hasTable('bulties') && !Schema::hasColumn('bulties', 'toll_invoice_id')) {
            Schema::table('bulties', function (Blueprint $table) {
                $table->unsignedBigInteger('toll_invoice_id')->nullable();
                $table->foreign('toll_invoice_id')->references('id')->on('invoices')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bulties') && Schema::hasColumn('bulties', 'toll_invoice_id')) {
            Schema::table('bulties', function (Blueprint $table) {
                $table->dropForeign(['toll_invoice_id']);
                $table->dropColumn('toll_invoice_id');
            });
        }
    }
};
