<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulty_items', function (Blueprint $table) {
            $table->decimal('freight_per_mt', 10, 2)->default(0)->after('unit');
        });

        DB::table('bulty_items')->update([
            'freight_per_mt' => DB::raw('freight_per_kg'),
        ]);

        Schema::table('bulty_items', function (Blueprint $table) {
            $table->dropColumn('freight_per_kg');
        });
    }

    public function down(): void
    {
        Schema::table('bulty_items', function (Blueprint $table) {
            $table->decimal('freight_per_kg', 10, 2)->default(0)->after('unit');
        });

        DB::table('bulty_items')->update([
            'freight_per_kg' => DB::raw('freight_per_mt'),
        ]);

        Schema::table('bulty_items', function (Blueprint $table) {
            $table->dropColumn('freight_per_mt');
        });
    }
};
