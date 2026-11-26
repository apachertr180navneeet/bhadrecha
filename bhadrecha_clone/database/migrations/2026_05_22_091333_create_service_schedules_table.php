<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('service_type');
            $table->date('scheduled_date')->nullable();
            $table->decimal('scheduled_km', 12, 2)->nullable();
            $table->date('last_service_date')->nullable();
            $table->decimal('last_service_km', 12, 2)->nullable();
            $table->integer('interval_days')->nullable();
            $table->decimal('interval_km', 12, 2)->nullable();
            $table->enum('status', ['upcoming', 'overdue', 'completed', 'cancelled'])->default('upcoming');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_schedules');
    }
};
