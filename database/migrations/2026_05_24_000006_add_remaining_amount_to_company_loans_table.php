<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_loans', function (Blueprint $table) {
            $table->decimal('remaining_amount', 12, 2)->default(0)->after('emi_amount');
        });
    }

    public function down(): void
    {
        Schema::table('company_loans', function (Blueprint $table) {
            $table->dropColumn('remaining_amount');
        });
    }
};
