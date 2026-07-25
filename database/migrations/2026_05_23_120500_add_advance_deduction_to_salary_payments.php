<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->decimal('advance_deduction', 10, 2)->default(0)->after('incentives_total');
        });

        Schema::table('employee_advances', function (Blueprint $table) {
            $table->foreignId('salary_payment_id')->nullable()->after('approved_by')->constrained('salary_payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn('advance_deduction');
        });

        Schema::table('employee_advances', function (Blueprint $table) {
            $table->dropForeign(['salary_payment_id']);
            $table->dropColumn('salary_payment_id');
        });
    }
};
