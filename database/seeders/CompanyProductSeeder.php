<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class CompanyProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        DB::table('companies')->insert([
            [
                'name' => 'شركة الحياة للأدوية',
                'address' => 'القاهرة - مصر',
                'phone' => '01000000001',
                'email' => 'hayah@example.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'شركة المستقبل فارما',
                'address' => 'الجيزة - مصر',
                'phone' => '01000000002',
                'email' => 'mostaqbal@example.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'شركة النور للأدوية',
                'address' => 'الإسكندرية - مصر',
                'phone' => '01000000003',
                'email' => 'nour@example.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'شركة الشفاء فارما',
                'address' => 'المنصورة - مصر',
                'phone' => '01000000004',
                'email' => 'shefa@example.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'شركة الهدى للأدوية',
                'address' => 'طنطا - مصر',
                'phone' => '01000000005',
                'email' => 'hoda@example.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
