<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Steel Rods',       'description' => 'TMT steel reinforcement bars'],
            ['name' => 'Cement',            'description' => 'Portland cement bags'],
            ['name' => 'Bricks',            'description' => 'Clay bricks'],
            ['name' => 'Sand',              'description' => 'Construction sand'],
            ['name' => 'Aggregate',         'description' => 'Crushed stone aggregate'],
            ['name' => 'Tiles',             'description' => 'Ceramic/porcelain tiles'],
            ['name' => 'Paint',             'description' => 'Emulsion/enamel paint buckets'],
            ['name' => 'Pipes',             'description' => 'PVC/GI pipes'],
            ['name' => 'Electrical Cable',  'description' => 'Copper/aluminum electrical cables'],
            ['name' => 'Furniture',         'description' => 'Office/home furniture'],
            ['name' => 'Food Grains',       'description' => 'Rice, wheat, pulses etc.'],
            ['name' => 'Fertilizer',        'description' => 'Chemical/organic fertilizer bags'],
        ];

        foreach ($items as $i) {
            Item::firstOrCreate(['name' => $i['name']], $i);
        }
    }
}
