<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_advance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('builty_id')->constrained('bulties')->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->foreignId('fuel_company_id')->nullable()->constrained('fuel_companies');
            $table->foreignId('fuel_pump_id')->nullable()->constrained('fuel_pumps');
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_advance_details');
    }
};
