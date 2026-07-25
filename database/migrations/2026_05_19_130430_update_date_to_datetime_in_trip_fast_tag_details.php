<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE trip_fast_tag_details MODIFY COLUMN date DATETIME NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE trip_fast_tag_details MODIFY COLUMN date DATE NULL');
    }
};
