<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_adblue_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('builty_id')->constrained('bulties')->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->foreignId('adblue_company_id')->nullable()->constrained('adblue_companies');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('km', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_adblue_details');
    }
};
