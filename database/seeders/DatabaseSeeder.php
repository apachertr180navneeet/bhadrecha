<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolePermissionSeeder::class,
            CitySeeder::class,
            SplashScreenSeeder::class,
            PackagingSeeder::class,
            UnitSeeder::class,
            ItemSeeder::class,
            TyreBrandSeeder::class,
            TyreModelSeeder::class,
            TyreSizeSeeder::class,
            DocumentCategorySeeder::class,
            MenuSeeder::class,
            PageSeeder::class,
        ]);
    }
}
