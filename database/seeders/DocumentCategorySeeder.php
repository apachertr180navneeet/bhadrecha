<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentCategory;
use Illuminate\Support\Str;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Company Documents',
            'HR Documents',
            'Employee Documents',
            'Customer Documents',
            'Vendor Documents',
            'Legal Documents',
            'GST Documents',
            'Tax Documents',
            'Insurance',
            'Vehicle Documents',
            'Transport Documents',
            'Agreements',
            'Certificates',
            'Licenses',
            'Bank Documents',
            'Other',
        ];

        foreach ($categories as $index => $catName) {
            DocumentCategory::firstOrCreate(
                ['name' => $catName],
                [
                    'company_id' => null, // Global default master
                    'slug' => Str::slug($catName),
                    'description' => "Standard category for {$catName}",
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
