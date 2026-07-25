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
        Schema::table('bulty_details', function (Blueprint $table) {
            $table->decimal('ul_rate', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bulty_details', function (Blueprint $table) {
            $table->dropColumn('ul_rate');
        });
    }
};
