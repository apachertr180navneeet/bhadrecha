<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_branch_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained('bank_masters')->onDelete('cascade');
            $table->string('branch_name');
            $table->string('ifsc')->unique();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_branch_masters');
    }
};
