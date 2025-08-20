<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        Category::create([

            'name'        => 'vitamin',
            'position'    => 1,
            'active'      => true,
            'show_home'   => true,
        ]);

     Category::create([

            'name'        => 'vitamin',
            'position'    => 2,
            'active'      => true,
            'show_home'   => true,
        ]);
    }
}
