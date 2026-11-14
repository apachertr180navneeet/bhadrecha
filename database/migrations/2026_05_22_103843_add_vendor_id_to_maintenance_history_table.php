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
        Schema::table('maintenance_history', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('spare_part_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_history', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');
        });
    }
};
