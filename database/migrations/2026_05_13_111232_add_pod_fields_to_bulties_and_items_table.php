<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            if (!Schema::hasColumn('bulties', 'consignor_pod')) {
                $table->string('consignor_pod')->nullable()->after('material_document_status');
            }
            if (!Schema::hasColumn('bulties', 'consignee_pod')) {
                $table->string('consignee_pod')->nullable()->after('consignor_pod');
            }
        });

        Schema::table('bulty_items', function (Blueprint $table) {
            if (!Schema::hasColumn('bulty_items', 'pod_file')) {
                $table->string('pod_file')->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropColumn(['consignor_pod', 'consignee_pod']);
        });

        Schema::table('bulty_items', function (Blueprint $table) {
            $table->dropColumn('pod_file');
        });
    }
};
