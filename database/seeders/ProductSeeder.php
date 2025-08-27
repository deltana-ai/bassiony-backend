<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name'        => 'Panadol',
            'description' => 'مسكن للصداع والآلام',
            'category_id' => 1,
            'brand_id'    => 1,
            'position'    => 1,
            'active'      => true,
            'show_home'   => true,
            'rating'      => 4.5,
            'rating_count'=> 100,
            'price'         => 5,
        ]);

        Product::create([
            'name'        => 'Vitamin C',
            'description' => 'مكمل غذائي يقوي المناعة',
            'category_id' => 2,
            'brand_id'    => 1,
            'position'    => 2,
            'active'      => true,
            'show_home'   => false,
            'rating'      => 4.2,
            'rating_count'=> 50,
            'price'         => 10,
        ]);
    }
}
