<?php

namespace Database\Seeders;

use App\Models\TyreBrand;
use App\Models\TyreModel;
use App\Models\TyreSize;
use Illuminate\Database\Seeder;

class TyreSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commonSizes = [
            ['name' => '295/80 R22.5', 'code' => '295-80-R22.5', 'description' => 'Heavy Duty Tubeless Radial Truck Tyre Size'],
            ['name' => '10.00 R20', 'code' => '10.00-R20', 'description' => 'Standard Tube-Type Radial Commercial Size'],
            ['name' => '11 R22.5', 'code' => '11-R22.5', 'description' => 'Long Distance Highway Radial Tyre Size'],
            ['name' => '315/80 R22.5', 'code' => '315-80-R22.5', 'description' => 'Extra Heavy Duty Tipper & Trailer Size'],
            ['name' => '295/90 R20', 'code' => '295-90-R20', 'description' => 'High Load Capacity Radial Size'],
            ['name' => '10.00-20 16PR', 'code' => '10.00-20-16PR', 'description' => 'Heavy Duty Bias Ply Tyre Size'],
            ['name' => '8.25 R20', 'code' => '8.25-R20', 'description' => 'Medium Commercial Vehicle Tyre Size'],
            ['name' => '7.50 R16', 'code' => '7.50-R16', 'description' => 'Light Commercial Vehicle Tyre Size'],
            ['name' => '12 R22.5', 'code' => '12-R22.5', 'description' => 'Heavy Haulage Tubeless Size'],
            ['name' => '9.00 R20', 'code' => '9.00-R20', 'description' => 'Standard Bus and Medium Truck Size'],
        ];

        // Seed common sizes linked to available models
        $models = TyreModel::with('brand')->get();

        if ($models->isNotEmpty()) {
            foreach ($models as $index => $model) {
                // Assign 2-3 sizes to each model
                $sizesForModel = array_slice($commonSizes, $index % 5, 3);
                foreach ($sizesForModel as $sizeData) {
                    TyreSize::updateOrCreate(
                        [
                            'tyre_brand_id' => $model->tyre_brand_id,
                            'tyre_model_id' => $model->id,
                            'name' => $sizeData['name'],
                        ],
                        [
                            'code' => $sizeData['code'],
                            'description' => $sizeData['description'],
                            'status' => 'active',
                        ]
                    );
                }
            }
        } else {
            // Standalone sizes
            foreach ($commonSizes as $sizeData) {
                TyreSize::updateOrCreate(
                    ['name' => $sizeData['name']],
                    [
                        'code' => $sizeData['code'],
                        'description' => $sizeData['description'],
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
