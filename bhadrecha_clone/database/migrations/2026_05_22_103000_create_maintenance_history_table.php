<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('spare_part_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_type');
            $table->date('service_date');
            $table->decimal('current_km', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('vendor_name')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->date('next_service_date')->nullable();
            $table->decimal('next_service_km', 12, 2)->nullable();
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['company_id', 'branch_id', 'vehicle_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_history');
    }
};
