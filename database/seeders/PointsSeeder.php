<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        // إدخال البيانات الخاصة بـ "earned"
        DB::table('points')->insert([
            'user_id' => 1, // تحديد الـ user_id
            'type' => 'earned',
            'amount' => 20, // إجمالي النقاط
            'source_name' => 'System', // يمكنك تخصيص المصدر
            'expires_at' => null, // تحديد تاريخ انتهاء الصلاحية إذا كان موجودًا
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // إدخال البيانات الخاصة بـ "spent"
        DB::table('points')->insert([
            'user_id' => 1, 
            'type' => 'spent',
            'amount' => 340, 
            'source_name' => 'System', 
            'expires_at' => null, // 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // // إدخال البيانات الخاصة بـ "expired"
        // DB::table('points')->insert([
        //     'user_id' => 1, 
        //     'type' => 'expired',
        //     'amount' =>230, 
        //     'source_name' => 'System', 
        //     'expires_at' => null, 
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        // ]);
    }
}
