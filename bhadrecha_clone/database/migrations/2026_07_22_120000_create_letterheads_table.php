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
        Schema::create('letterheads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('letter_no')->unique();
            $table->date('letter_date');
            $table->string('recipient_name');
            $table->string('recipient_designation')->nullable();
            $table->string('recipient_company')->nullable();
            $table->text('recipient_address')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('subject');
            $table->longText('content');
            $table->string('signatory_name')->nullable();
            $table->string('signatory_designation')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letterheads');
    }
};
