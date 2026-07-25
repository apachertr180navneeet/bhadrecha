<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulty_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulty_id')->constrained('bulties')->onDelete('cascade');

            $table->date('posting_date')->nullable();
            $table->string('po_no')->nullable();
            $table->string('mat_doc')->nullable();
            $table->string('gate_entry_no')->nullable();
            $table->string('challan_no')->nullable();
            $table->date('gate_out_date')->nullable();
            $table->string('invoice_doc')->nullable();
            $table->date('invoice_date')->nullable();
            $table->time('invoice_time')->nullable();
            $table->string('grn_no')->nullable();
            $table->date('grn_date')->nullable();
            $table->time('grn_time')->nullable();
            $table->decimal('recd_qty', 12, 2)->default(0);
            $table->time('arrival_time')->nullable();
            $table->string('shortage_grn_no')->nullable();
            $table->date('shortage_grn_date')->nullable();
            $table->decimal('short_qty', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulty_details');
    }
};
