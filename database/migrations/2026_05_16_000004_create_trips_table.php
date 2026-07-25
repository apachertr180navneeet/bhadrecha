<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('builty_id')->constrained('bulties')->cascadeOnDelete();
            $table->decimal('fasttag_total_amount', 10, 2)->default(0);
            $table->decimal('fuel_amount', 10, 2)->default(0);
            $table->decimal('other_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
