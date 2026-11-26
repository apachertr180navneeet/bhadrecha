<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breakdowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->date('breakdown_date');
            $table->time('breakdown_time')->nullable();
            $table->string('location');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('description')->nullable();
            $table->string('issue_type');
            $table->enum('severity', ['minor', 'major', 'critical'])->default('major');
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('repair_cost', 12, 2)->nullable();
            $table->decimal('downtime_hours', 8, 2)->nullable();
            $table->enum('status', ['reported', 'in_progress', 'resolved', 'towed'])->default('reported');
            $table->text('resolution_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['company_id', 'branch_id', 'vehicle_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breakdowns');
    }
};
