<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->unsignedBigInteger('consignor_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('consignee_id')->nullable()->after('consignor_id');
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropColumn(['consignor_id', 'consignee_id']);
        });
    }
};
