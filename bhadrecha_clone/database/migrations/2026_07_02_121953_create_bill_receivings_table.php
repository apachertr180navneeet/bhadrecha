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
        Schema::create('bill_receivings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('date');
            $table->decimal('receiving_amount', 12, 2)->default(0.00);
            $table->decimal('receiving_gst', 12, 2)->default(0.00);
            $table->decimal('tds', 12, 2)->default(0.00);
            $table->decimal('deduction', 12, 2)->default(0.00);
            $table->string('deduction_reason')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_receivings');
    }
};
