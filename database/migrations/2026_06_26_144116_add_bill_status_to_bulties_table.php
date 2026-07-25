<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->enum('bill_status', ['unbilled', 'billed'])->default('unbilled')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropColumn('bill_status');
        });
    }
};
