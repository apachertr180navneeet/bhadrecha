<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fastag_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number', 50)->nullable()->index();
            $table->dateTime('sync_from')->nullable();
            $table->dateTime('sync_to')->nullable();
            $table->string('sync_status', 50)->default('PENDING')->index(); // PENDING, SUCCESS, FAILED, PARTIAL
            $table->unsignedInteger('total_records_received')->default(0);
            $table->unsignedInteger('total_records_saved')->default(0);
            $table->text('error_message')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['sync_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fastag_sync_logs');
    }
};
