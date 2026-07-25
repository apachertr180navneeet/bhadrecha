<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tyre_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tyre_brand_id')->nullable()->constrained('tyre_brands')->onDelete('cascade');
            $table->foreignId('tyre_model_id')->nullable()->constrained('tyre_models')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tyre_sizes');
    }
};
