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
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->string('deduction_type', 20)->default('full')->after('amount');
            $table->decimal('monthly_deduction', 10, 2)->nullable()->after('deduction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->dropColumn(['deduction_type', 'monthly_deduction']);
        });
    }
};
