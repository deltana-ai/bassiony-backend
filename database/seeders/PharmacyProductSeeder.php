<?php 



// database/seeders/PharmacyProductSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pharmacy_product')->insert([
            [
                'pharmacy_id' => 1,
                'product_id'  => 1, 
                'price'       => 25.00,
                'stock'       => 100,
            ],
            [
                'pharmacy_id' => 1,
                'product_id'  => 1, 
                'price'       => 40.00,
                'stock'       => 50,
            ],
            [
                'pharmacy_id' => 2,
                'product_id'  => 1,
                'price'       => 23.00,
                'stock'       => 200,
            ],
        ]);
    }
}
