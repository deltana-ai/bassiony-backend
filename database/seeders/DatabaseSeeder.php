<?php

namespace Database\Seeders;

use App\Models\ContactPeople;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // $this->call();
        // $this->call();
        // User::factory(100)->create();
        $this->call([
            CategoriesSeeder::class,
            BrandSeeder::class,
            PharmacySeeder::class,
        ProductSeeder::class,
        PharmacyProductSeeder::class,
        AdminSeeder::class,
        BaseSeeder::class,
    ]);

    }
}



