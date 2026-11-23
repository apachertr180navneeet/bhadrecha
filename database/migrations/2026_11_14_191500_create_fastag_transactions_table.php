<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fastag_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->nullable()->index();
            $table->string('vehicle_number', 50)->index();
            $table->string('transaction_id', 150)->unique();
            $table->string('tag_id', 100)->nullable()->index();
            $table->string('toll_plaza_name', 255)->nullable()->index();
            $table->string('transaction_type', 50)->default('DEBIT')->index(); // DEBIT, CREDIT, REFUND, etc.
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->dateTime('transaction_date')->index();
            $table->string('transaction_status', 50)->default('SUCCESS')->index(); // SUCCESS, FAILED, PENDING, DISPUTED
            $table->string('lane_code', 50)->nullable();
            $table->string('plaza_code', 50)->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['vehicle_number', 'transaction_date']);
            $table->index(['toll_plaza_name', 'transaction_date']);
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fastag_transactions');
    }
};
