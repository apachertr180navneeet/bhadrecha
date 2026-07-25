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
            $table->decimal('tds', 12, 2)->default(0.00)->after('total_amount');
            $table->decimal('deduction', 12, 2)->default(0.00)->after('tds');
            $table->decimal('receiving_amount', 12, 2)->default(0.00)->after('deduction');
            $table->decimal('receiving_gst', 12, 2)->default(0.00)->after('receiving_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['tds', 'deduction', 'receiving_amount', 'receiving_gst']);
        });
    }
};
