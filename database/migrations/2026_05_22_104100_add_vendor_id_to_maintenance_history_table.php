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
        $hasColumn = Schema::hasColumn('maintenance_history', 'vendor_id');
        $hasFk = false;

        try {
            $fks = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'maintenance_history' 
                  AND CONSTRAINT_NAME = 'maintenance_history_vendor_id_foreign'
            ");
            $hasFk = !empty($fks);
        } catch (\Throwable $e) {
            $hasFk = false;
        }

        try {
            Schema::table('maintenance_history', function (Blueprint $table) use ($hasColumn, $hasFk) {
                if (!$hasColumn) {
                    $table->foreignId('vendor_id')->nullable()->after('spare_part_id')->constrained()->nullOnDelete();
                } elseif (!$hasFk) {
                    $table->foreign('vendor_id')->references('id')->on('vendors')->nullOnDelete();
                }
            });
        } catch (\Throwable $e) {
            // Handle error 1022 duplicate key / constraint gracefully if already present in MySQL
            if (str_contains($e->getMessage(), '1022') || str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'already exists')) {
                // Constraint/column already exists, safe to proceed
            } else {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        $hasFk = false;
        try {
            $fks = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'maintenance_history' 
                  AND CONSTRAINT_NAME = 'maintenance_history_vendor_id_foreign'
            ");
            $hasFk = !empty($fks);
        } catch (\Throwable $e) {
            $hasFk = false;
        }

        try {
            Schema::table('maintenance_history', function (Blueprint $table) use ($hasFk) {
                if ($hasFk) {
                    $table->dropForeign(['vendor_id']);
                }
                if (Schema::hasColumn('maintenance_history', 'vendor_id')) {
                    $table->dropColumn('vendor_id');
                }
            });
        } catch (\Throwable $e) {
            // Ignore if foreign key was already dropped
        }
    }
};
