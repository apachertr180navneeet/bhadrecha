<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            if (!Schema::hasColumn('bulties', 'pod_document')) {
                $table->string('pod_document')->nullable()->after('consignee_pod');
            }
            if (!Schema::hasColumn('bulties', 'pod_document_status')) {
                $table->boolean('pod_document_status')->default(false)->after('pod_document');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropColumn(['pod_document', 'pod_document_status']);
        });
    }
};
