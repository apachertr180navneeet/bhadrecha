<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('format_name');
            $table->foreignId('depot_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('party_id')->nullable()->constrained('consignors')->nullOnDelete();
            $table->json('visible_fields')->nullable();
            $table->json('field_order')->nullable();
            $table->boolean('grn_new_page')->default(false);
            $table->foreignId('gst_master_id')->nullable()->constrained('gst_masters')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_formats');
    }
};
