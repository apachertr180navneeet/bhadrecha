<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Kg',    'description' => 'Kilogram'],
            ['name' => 'Gm',    'description' => 'Gram'],
            ['name' => 'Tonne', 'description' => 'Metric Tonne (1000 Kg)'],
            ['name' => 'Lb',    'description' => 'Pound'],
            ['name' => 'Nos',   'description' => 'Numbers / Pieces'],
            ['name' => 'Pcs',   'description' => 'Pieces'],
            ['name' => 'Box',   'description' => 'Box count'],
            ['name' => 'Ltr',   'description' => 'Litre'],
            ['name' => 'Ml',    'description' => 'Millilitre'],
            ['name' => 'Mtr',   'description' => 'Metre'],
            ['name' => 'Sq Ft', 'description' => 'Square Feet'],
            ['name' => 'Cu M',  'description' => 'Cubic Metre'],
        ];

        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u['name']], $u);
        }
    }
}
