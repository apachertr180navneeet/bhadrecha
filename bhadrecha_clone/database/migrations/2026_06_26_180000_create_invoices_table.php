<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('consignor_id')->nullable();
                $table->string('consignor_name')->nullable();
                $table->string('from_city_name')->nullable();
                $table->string('to_city_name')->nullable();
                $table->decimal('total_freight', 12, 2)->default(0.00);
                $table->decimal('total_gst', 12, 2)->default(0.00);
                $table->decimal('total_other', 12, 2)->default(0.00);
                $table->string('invoice_no')->unique();
                $table->string('bill_number')->nullable();
                $table->date('invoice_date');
                $table->decimal('total_amount', 14, 2)->default(0.00);
                $table->text('amount_in_words')->nullable();
                $table->longText('visible_fields')->nullable();
                $table->boolean('grn_new_page')->default(false);
                $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('gst_master_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Do not drop since it is a pre-existing table
    }
};
