<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->decimal('per_day_rate', 10, 2)->default(0)->after('attended_days');
            $table->decimal('attendance_salary', 10, 2)->default(0)->after('per_day_rate');
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn(['per_day_rate', 'attendance_salary']);
        });
    }
};
