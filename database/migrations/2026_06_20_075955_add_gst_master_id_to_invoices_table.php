<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'gst_master_id')) {
                $table->foreignId('gst_master_id')->nullable()->constrained('gst_masters')->nullOnDelete()->after('branch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'gst_master_id')) {
                $table->dropForeign(['gst_master_id']);
                $table->dropColumn('gst_master_id');
            }
        });
    }
};
