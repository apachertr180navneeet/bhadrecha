<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
                $table->decimal('amount', 12, 2)->default(0);
                $table->date('date');
                $table->text('remarks')->nullable();
                $table->string('attachment')->nullable();
                $table->string('status')->nullable()->default('approved');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
