<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained('bank_masters')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('bank_branch_masters')->onDelete('cascade');
            $table->string('loan_id')->unique();
            $table->integer('tenure_months');
            $table->integer('given_emi_count')->default(0);
            $table->decimal('loan_amount', 12, 2);
            $table->text('tenure_calculation')->nullable();
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('total_interest', 12, 2)->default(0);
            $table->decimal('given_amount', 12, 2);
            $table->decimal('emi_amount', 10, 2)->default(0);
            $table->date('pending_emi_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_loans');
    }
};
