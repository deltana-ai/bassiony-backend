<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // Add other seeders here
            UserWithTokenSeeder::class,
            UserSeeder::class,
            PointsSeeder::class,
        ]);

    }
}
