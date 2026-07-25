<?php

namespace Database\Seeders;

use App\Models\TyreBrand;
use App\Models\TyreModel;
use Illuminate\Database\Seeder;

class TyreModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brandModels = [
            'Apollo Tyres' => [
                ['name' => 'EnduRace RD', 'code' => 'ENDURACE-RD', 'description' => 'Drive Axle Radial Tyre for Heavy Commercial Vehicles'],
                ['name' => 'EnduRace RA', 'code' => 'ENDURACE-RA', 'description' => 'All Position Heavy Duty Radial Tyre'],
                ['name' => 'Amar Gold', 'code' => 'AMAR-GOLD', 'description' => 'High mileage bias ply commercial tyre'],
                ['name' => 'EnduTrax MA', 'code' => 'ENDUTRAX-MA', 'description' => 'Mining & Construction Heavy Duty Tyre'],
            ],
            'MRF' => [
                ['name' => 'Muscleflex', 'code' => 'MUSCLEFLEX', 'description' => 'Premium Drive Axle Radial Tyre'],
                ['name' => 'Super Lug', 'code' => 'SUPER-LUG', 'description' => 'High traction heavy load bias tyre'],
                ['name' => 'Savari', 'code' => 'SAVARI', 'description' => 'Light Commercial Vehicle Radial Tyre'],
                ['name' => 'Steel Muscle S3K4', 'code' => 'STEEL-MUSCLE', 'description' => 'Steel Belted All-Wheel Radial Tyre'],
            ],
            'CEAT' => [
                ['name' => 'Winmile AW', 'code' => 'WINMILE-AW', 'description' => 'All Weather Long Haul Radial Tyre'],
                ['name' => 'Winload X3', 'code' => 'WINLOAD-X3', 'description' => 'High Load Heavy Truck Radial Tyre'],
                ['name' => 'Milaze', 'code' => 'MILAZE', 'description' => 'High Mileage Commercial Bias Tyre'],
                ['name' => 'Mile XL', 'code' => 'MILE-XL', 'description' => 'Extra Load Steering Tyre'],
            ],
            'Michelin' => [
                ['name' => 'X Multi Z', 'code' => 'X-MULTI-Z', 'description' => 'All-position Tubeless Radial Tyre'],
                ['name' => 'X Multi D', 'code' => 'X-MULTI-D', 'description' => 'Drive-axle Heavy Commercial Radial Tyre'],
                ['name' => 'X Works HD', 'code' => 'X-WORKS-HD', 'description' => 'On/Off Road Mining Heavy Duty Tyre'],
            ],
            'Goodyear' => [
                ['name' => 'Armor Grip', 'code' => 'ARMOR-GRIP', 'description' => 'Puncture resistant commercial tyre'],
                ['name' => 'Marathon LHD', 'code' => 'MARATHON-LHD', 'description' => 'Long Distance Heavy Haul Tyre'],
                ['name' => 'Omnitrac Heavy', 'code' => 'OMNITRAC-HD', 'description' => 'Mixed service construction tyre'],
            ],
            'Bridgestone' => [
                ['name' => 'R156', 'code' => 'R156', 'description' => 'Highway Steering & All Position Radial Tyre'],
                ['name' => 'M729', 'code' => 'M729', 'description' => 'Premium Drive Axle Radial Tyre'],
                ['name' => 'L301', 'code' => 'L301', 'description' => 'Heavy Duty Bias Truck Tyre'],
            ],
            'JK Tyre' => [
                ['name' => 'JETSTEEL JDH3', 'code' => 'JETSTEEL-JDH3', 'description' => 'Drive Axle Radial Tyre'],
                ['name' => 'JETWAY JUH5', 'code' => 'JETWAY-JUH5', 'description' => 'All Position Steering Radial Tyre'],
                ['name' => 'Jet Xtra', 'code' => 'JET-XTRA', 'description' => 'Heavy Load Capacity Tyre'],
            ],
        ];

        foreach ($brandModels as $brandName => $models) {
            $brand = TyreBrand::where('name', $brandName)->first();
            if ($brand) {
                foreach ($models as $modelData) {
                    TyreModel::updateOrCreate(
                        ['tyre_brand_id' => $brand->id, 'name' => $modelData['name']],
                        [
                            'code' => $modelData['code'],
                            'description' => $modelData['description'],
                            'status' => 'active',
                        ]
                    );
                }
            }
        }
    }
}
