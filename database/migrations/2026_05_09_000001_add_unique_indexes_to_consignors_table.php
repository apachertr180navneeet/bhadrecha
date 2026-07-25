<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate phones keeping the earliest record (MySQL workaround for subquery)
        DB::delete('DELETE FROM consignors WHERE id NOT IN (SELECT id FROM (SELECT MIN(id) as id FROM consignors GROUP BY phone) as tmp)');

        Schema::table('consignors', function (Blueprint $table) {
            $table->unique('phone');
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('consignors', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropUnique(['email']);
        });
    }
};
