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
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('document_number')->unique();
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
                $table->string('name');
                $table->foreignId('category_id')->constrained('document_categories')->onDelete('cascade');
                $table->foreignId('folder_id')->nullable()->constrained('document_folders')->onDelete('set null');
                $table->text('description')->nullable();
                $table->json('tags')->nullable();
                $table->string('version')->default('1.0');
                $table->string('file_name');
                $table->string('original_file_name');
                $table->string('file_extension');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('storage_path');
                $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
                $table->string('department')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('effective_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->enum('status', ['active', 'archived', 'expired', 'draft'])->default('active');
                $table->text('remarks')->nullable();
                $table->unsignedInteger('downloads_count')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'branch_id', 'category_id', 'folder_id']);
                $table->index(['expiry_date', 'status']);
                $table->index('file_extension');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
