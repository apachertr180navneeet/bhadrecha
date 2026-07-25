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
        Schema::table('trip_fast_tag_details', function (Blueprint $table) {
            $table->string('location', 255)->nullable()->after('transaction_id');
            $table->decimal('one_way', 10, 2)->default(0)->after('location');
            $table->decimal('return', 10, 2)->default(0)->after('one_way');
        });
    }

    public function down(): void
    {
        Schema::table('trip_fast_tag_details', function (Blueprint $table) {
            $table->dropColumn(['location', 'one_way', 'return']);
        });
    }
};
