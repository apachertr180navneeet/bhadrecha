<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tyre_management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('tyre_position');
            $table->string('tyre_brand');
            $table->string('tyre_size');
            $table->string('tyre_model')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->default(0);
            $table->date('installation_date')->nullable();
            $table->decimal('installation_km', 10, 2)->nullable();
            $table->date('removal_date')->nullable();
            $table->decimal('removal_km', 10, 2)->nullable();
            $table->string('removal_reason')->nullable();
            $table->decimal('tread_depth_new', 5, 2)->nullable();
            $table->decimal('tread_depth_current', 5, 2)->nullable();
            $table->decimal('pressure_psi', 5, 1)->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tyre_management');
    }
};
