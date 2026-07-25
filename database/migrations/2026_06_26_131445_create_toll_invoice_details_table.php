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
        Schema::create('toll_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('toll_invoice_id');
            $table->unsignedBigInteger('builty_id');
            $table->string('location')->nullable();
            $table->decimal('one_way', 10, 2)->default(0);
            $table->decimal('return_amount', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('toll_invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('builty_id')->references('id')->on('bulties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toll_invoice_details');
    }
};
