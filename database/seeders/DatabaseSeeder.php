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
        $this->call(CountryDatabaseSeeder::class);
        User::factory(10)->create();
        $this->call(AdminSeeder::class);
        $this->call(PermissionDatabaseSeeder::class);
        $this->call(RoleDatabaseSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(SettingDatabaseSeeder::class);
        $this->call(EmailTemplateSeeder::class);
        $this->call(TeamSeeder::class);
        $this->call(BaseSeeder::class);
        User::factory(100)->create();
        $this->call(EventsTableSeeder::class);
        $this->call(ContactPeopleSeeder::class);
        $this->call(PoliciesTableSeeder::class);
        $this->call(ArticlesTableSeeder::class);
    }
}



