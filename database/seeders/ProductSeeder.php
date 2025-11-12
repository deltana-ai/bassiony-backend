<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name_ar' => 'بانادول',
            'name_en' => 'Panadol',
            'description' => 'مسكن للصداع والآلام',
            'category_id' => 1,
            'brand_id'    => 1,
            'position'    => 1,
            'bar_code'    => "0123456789",
            'active_ingredients' => 'Paracetamol, Acetaminophen',
            'scientific_name' => 'Paracetamol',
            'dosage_form' => 'Tablet',
            'gtin' => '1234567890123',
            'active'      => true,
            'show_home'   => true,
            'rating'      => 4.5,
            'rating_count'=> 100,
            'price'         => 5,
        ]);

        Product::create([
            'name_en'        => 'Vitamin C',
            'name_ar' => 'فيتامين C',
            'description' => 'مكمل غذائي يقوي المناعة',
            'category_id' => 2,
            'brand_id'    => 1,
            'position'    => 2,
            'bar_code'    => "9876543210",
            'active_ingredients' => 'Vitamin C',
            'scientific_name' => 'Vitamin C',
            'dosage_form' => 'Tablet',
            'gtin' => '9876543210123',
            'active'      => true,
            'show_home'   => false,
            'rating'      => 4.2,
            'rating_count'=> 50,
            'price'         => 10,
        ]);
    }
}
