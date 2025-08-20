<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        Brand::create([
            'name'        => 'lemtless',
            'position'    => 1,
            'active'      => true,
            'show_home'   => true,
        ]);

     Brand::create([

            'name'        => 'omga3',
            'position'    => 2,
            'active'      => true,
            'show_home'   => true,
        ]);
    }
}
