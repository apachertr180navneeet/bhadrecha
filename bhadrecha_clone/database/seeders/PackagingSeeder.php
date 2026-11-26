<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Packaging;

class PackagingSeeder extends Seeder
{
    public function run(): void
    {
        $packagings = [
            ['name' => 'Box',           'description' => 'Standard cardboard box'],
            ['name' => 'Carton',        'description' => 'Carton box packaging'],
            ['name' => 'Pallet',        'description' => 'Wooden or plastic pallet'],
            ['name' => 'Crate',         'description' => 'Wooden crate for heavy items'],
            ['name' => 'Drum',          'description' => 'Metal or plastic drum'],
            ['name' => 'Bag',           'description' => 'Jute or plastic bag'],
            ['name' => 'Bundle',        'description' => 'Items bundled together'],
            ['name' => 'Roll',          'description' => 'Rolled packaging'],
            ['name' => 'Container',     'description' => 'Shipping container'],
            ['name' => 'Loose',         'description' => 'Loose / no packaging'],
            ['name' => 'Polybag',       'description' => 'Polyethylene bag'],
            ['name' => 'Shrink Wrap',   'description' => 'Shrink-wrapped packaging'],
        ];

        foreach ($packagings as $p) {
            Packaging::firstOrCreate(['name' => $p['name']], $p);
        }
    }
}
