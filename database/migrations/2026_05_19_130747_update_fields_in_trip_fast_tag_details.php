<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE trip_fast_tag_details CHANGE COLUMN date transaction_time DATETIME NULL');
        DB::statement('ALTER TABLE trip_fast_tag_details DROP COLUMN location');
        DB::statement('ALTER TABLE trip_fast_tag_details ADD COLUMN description VARCHAR(255) NULL AFTER amount');
        DB::statement('ALTER TABLE trip_fast_tag_details ADD COLUMN transaction_id VARCHAR(255) NULL AFTER description');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE trip_fast_tag_details DROP COLUMN transaction_id');
        DB::statement('ALTER TABLE trip_fast_tag_details DROP COLUMN description');
        DB::statement('ALTER TABLE trip_fast_tag_details ADD COLUMN location VARCHAR(255) NULL AFTER transaction_time');
        DB::statement('ALTER TABLE trip_fast_tag_details CHANGE COLUMN transaction_time date DATETIME NULL');
    }
};
