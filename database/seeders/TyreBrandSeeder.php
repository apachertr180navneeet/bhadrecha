<?php

namespace Database\Seeders;

use App\Models\TyreBrand;
use Illuminate\Database\Seeder;

class TyreBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Apollo Tyres', 'code' => 'APOLLO', 'description' => 'Apollo Tyres Ltd - Leading Indian Tyre Manufacturer', 'status' => 'active'],
            ['name' => 'MRF', 'code' => 'MRF', 'description' => 'Madras Rubber Factory - Top Tyre Brand in India', 'status' => 'active'],
            ['name' => 'CEAT', 'code' => 'CEAT', 'description' => 'CEAT Limited - Premium Commercial & Radial Tyres', 'status' => 'active'],
            ['name' => 'Michelin', 'code' => 'MICHELIN', 'description' => 'Michelin Group - Global Leader in Tyre Technology', 'status' => 'active'],
            ['name' => 'Goodyear', 'code' => 'GOODYEAR', 'description' => 'Goodyear Tire & Rubber Company', 'status' => 'active'],
            ['name' => 'Bridgestone', 'code' => 'BRIDGESTONE', 'description' => 'Bridgestone Corporation - World-class Radial & Bias Tyres', 'status' => 'active'],
            ['name' => 'JK Tyre', 'code' => 'JKTYRE', 'description' => 'JK Tyre & Industries Ltd - Pioneer of Radial Tyres in India', 'status' => 'active'],
            ['name' => 'Yokohama', 'code' => 'YOKOHAMA', 'description' => 'Yokohama Rubber Company', 'status' => 'active'],
            ['name' => 'Continental', 'code' => 'CONTINENTAL', 'description' => 'Continental AG - High Performance Commercial Tyres', 'status' => 'active'],
            ['name' => 'Firestone', 'code' => 'FIRESTONE', 'description' => 'Firestone Tire and Rubber Company', 'status' => 'active'],
            ['name' => 'BKT', 'code' => 'BKT', 'description' => 'Balkrishna Industries Ltd - Heavy Duty & Off-Highway Tyres', 'status' => 'active'],
            ['name' => 'TVS Eurogrip', 'code' => 'TVSEUROGRIP', 'description' => 'TVS Srichakra Limited', 'status' => 'active'],
        ];

        foreach ($brands as $brand) {
            TyreBrand::updateOrCreate(
                ['name' => $brand['name']],
                $brand
            );
        }
    }
}
