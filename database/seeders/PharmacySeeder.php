<?php 



// database/seeders/PharmacyProductSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pharmacies')->insert([
            [
                'name'        => 'Pharmacy 1',
                'address'    => 'Location 1',
                'phone'       => '123456789',
            ],
            [
                'name'        => 'Pharmacy 2',
                'address'    => 'Location 2',
                'phone'       => '987654321',
            ],
        ]);
    }
}

      
