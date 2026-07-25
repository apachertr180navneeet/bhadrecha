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
        Schema::table('bulty_details', function (Blueprint $table) {
            $table->string('mn_no')->nullable();
            $table->string('bill_no')->nullable();
            $table->string('supplier_no')->nullable();
            $table->string('material_name')->nullable();
            $table->string('material_no')->nullable();
            $table->string('depot_name')->nullable();
            $table->decimal('billed_qty', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulty_details', function (Blueprint $table) {
            $table->dropColumn([
                'mn_no',
                'bill_no',
                'supplier_no',
                'material_name',
                'material_no',
                'depot_name',
                'billed_qty'
            ]);
        });
    }
};
