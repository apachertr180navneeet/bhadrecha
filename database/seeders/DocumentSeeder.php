<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\DocumentCategory;
use App\Models\DocumentFolder;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\UploadedFile;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DocumentCategorySeeder::class,
            DocumentPermissionSeeder::class,
        ]);

        $company = Company::first();
        $user = User::first();

        if (!$company || !$user) {
            return;
        }

        // Create sample nested folder tree
        $companyFolder = DocumentFolder::firstOrCreate([
            'company_id' => $company->id,
            'name' => 'Company Operations',
        ], [
            'slug' => 'company-operations',
            'status' => 'active',
        ]);

        $transportFolder = DocumentFolder::firstOrCreate([
            'company_id' => $company->id,
            'name' => 'Transport & Fleet',
            'parent_id' => $companyFolder->id,
        ], [
            'slug' => 'transport-fleet',
            'status' => 'active',
        ]);

        $insuranceFolder = DocumentFolder::firstOrCreate([
            'company_id' => $company->id,
            'name' => 'Insurance & RC',
            'parent_id' => $transportFolder->id,
        ], [
            'slug' => 'insurance-rc',
            'status' => 'active',
        ]);
    }
}
