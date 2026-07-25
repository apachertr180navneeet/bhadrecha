<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['drivers', 'vehicles'] as $table) {
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['company_id']);
                });
            } catch (\Exception $e) {
                // Foreign key may not exist
            }
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['branch_id']);
                });
            } catch (\Exception $e) {
                // Foreign key may not exist
            }
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropIndex($table . '_company_id_status_index');
                });
            } catch (\Exception $e) {
                // Index may not exist
            }
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn(['company_id', 'branch_id']);
                });
            } catch (\Exception $e) {
                // Columns may already be dropped
            }
        }
    }

    public function down(): void
    {
        foreach (['drivers', 'vehicles'] as $table) {
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                    $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
                    $t->index(['company_id', 'status']);
                });
            } catch (\Exception $e) {
                // Columns or indexes may already exist
            }
        }
    }
};
