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
            $table->json('grn_fields')->nullable();
            $table->json('grn_field_order')->nullable();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('grn_fields')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bill_formats', function (Blueprint $table) {
            $table->dropColumn(['grn_fields', 'grn_field_order']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('grn_fields');
        });
    }
};
