<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->string('material_document')->nullable()->after('share_token');
            $table->boolean('material_document_status')->default(false)->after('material_document');
        });
    }

    public function down(): void
    {
        Schema::table('bulties', function (Blueprint $table) {
            $table->dropColumn(['material_document', 'material_document_status']);
        });
    }
};
