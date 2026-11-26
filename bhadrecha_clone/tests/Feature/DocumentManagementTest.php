<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\DocumentCategory;
use App\Models\DocumentFolder;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_document_category_creation()
    {
        $category = DocumentCategory::create([
            'name' => 'Test Category',
            'description' => 'Test Category Description',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('document_categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_document_folder_creation()
    {
        $folder = DocumentFolder::create([
            'name' => 'Test Folder',
            'slug' => 'test-folder',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('document_folders', [
            'name' => 'Test Folder',
        ]);
    }
}
