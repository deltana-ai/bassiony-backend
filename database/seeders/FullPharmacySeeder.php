<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Pharmacy;
use App\Models\Offer;
use App\Models\PharmacyProduct;

class FullPharmacySeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. تصنيفات
            $categories = collect([
                'مسكنات',
                'مكملات غذائية',
                'فيتامينات',
                'مضادات حيوية',
            ])->map(function ($name, $i) {
                return Category::create([
                    'name' => $name,
                    'position' => $i + 1,
                    'active' => 1,
                    'show_home' => 1,
                ]);
            });

            // 2. برندات
            $brands = collect([
                'Panadol',
                'Centrum',
                'Pharco',
                'Vitamin C Brand',
            ])->map(function ($name, $i) {
                return Brand::create([
                    'name' => $name,
                    'position' => $i + 1,
                    'active' => 1,
                    'show_home' => 1,
                ]);
            });

            // 3. صيدليات
            $pharmacies = collect([
                'صيدلية الشفاء',
                'صيدلية النهدي',
                'صيدلية مصر',
            ])->map(function ($name) {
                return Pharmacy::create([
                    'name' => $name,
                    'address' => Str::random(10) . ' شارع',
                    'phone' => '01' . rand(100000000, 999999999),
                ]);
            });

            // 4. منتجات
            $products = collect();
            for ($i = 1; $i <= 20; $i++) {
                $products->push(Product::create([
                    'name' => 'منتج ' . $i,
                    'position' => $i,
                    'active' => 1,
                    'show_home' => rand(0, 1),
                    'description' => 'وصف منتج ' . $i,
                    'category_id' => $categories->random()->id,
                    'brand_id' => $brands->random()->id,
                ]));
            }

            // 5. ربط منتجات بصيدليات + إنشاء PharmacyProduct
            $pharmacyProducts = collect();

            foreach ($products as $product) {
                $pharmacy = $pharmacies->random();

                $pharmacyProduct = PharmacyProduct::create([
                    'pharmacy_id' => $pharmacy->id,
                    'product_id' => $product->id,
                    'price' => $price = rand(50, 200),
                    'quantity' => rand(10, 100),
                ]);

                $pharmacyProducts->push($pharmacyProduct);
            }

            // 6. عروض (Offers)
            foreach ($pharmacyProducts as $pharmacyProduct) {
                if (rand(0, 1)) {
                    Offer::create([
                        'pharmacy_product_id' => $pharmacyProduct->id,
                        'discount_price' => rand(5, $pharmacyProduct->price - 1),
                        'start_date' => Carbon::now()->subDays(rand(0, 5)),
                        'end_date' => Carbon::now()->addDays(rand(3, 10)),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd('Seeder Error:', $e->getMessage());
        }
    }
}
