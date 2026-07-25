<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_incentives', function (Blueprint $table) {
            $table->boolean('is_processed')->default(false)->after('reason');
            $table->foreignId('salary_payment_id')->nullable()->constrained()->nullOnDelete()->after('is_processed');
        });
    }

    public function down(): void
    {
        Schema::table('employee_incentives', function (Blueprint $table) {
            $table->dropForeign(['salary_payment_id']);
            $table->dropColumn(['is_processed', 'salary_payment_id']);
        });
    }
};
