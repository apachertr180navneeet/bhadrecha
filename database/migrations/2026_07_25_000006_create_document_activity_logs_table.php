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
        if (!Schema::hasTable('document_activity_logs')) {
            Schema::create('document_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('cascade');
                $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('action'); // upload, edit, delete, restore, download, preview, version_upload, category_create, folder_create
                $table->text('description')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'document_id', 'user_id', 'action'], 'doc_act_logs_comp_doc_usr_act_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_activity_logs');
    }
};
