<?php
namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Pharmacy;
use App\Models\PharmacyProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $categories = Category::factory()->count(3)->create();
        $brands = Brand::factory()->count(3)->create();
        $products = collect();
        foreach ($categories as $category) {
            foreach ($brands as $brand) {
                $products->push(Product::create([
                    'name'        => 'منتج ' . Str::random(5),
                    'position'    => rand(1, 10),
                    'active'      => true,
                    'show_home'   => true,
                    'description' => 'وصف المنتج',
                    'category_id' => $category->id,
                    'brand_id'    => $brand->id,
                ]));
            }
        }
        $pharmacies = Pharmacy::factory()->count(2)->create();
        $pharmacyProducts = collect();
        foreach ($pharmacies as $pharmacy) {
            foreach ($products as $product) {
                $pharmacyProducts->push(PharmacyProduct::create([
                    'pharmacy_id' => $pharmacy->id,
                    'product_id'  => $product->id,
                    'price'       => rand(50, 150),
                    'quantity'    => rand(10, 100),
                ]));
            }
        }
        foreach ($pharmacyProducts->random(5) as $pharmacyProduct) {
            Offer::create([
                'pharmacy_product_id' => $pharmacyProduct->id,
                'discount_price'      => $pharmacyProduct->price - rand(5, 20),
                'start_date'          => now()->subDays(rand(1, 10))->toDateString(),
                'end_date'            => now()->addDays(rand(5, 15))->toDateString(),
            ]);
        }
        $this->call([
            // Add other seeders here
            UserWithTokenSeeder::class,
            UserSeeder::class,
            PointsSeeder::class,
        ]);

    }
}
