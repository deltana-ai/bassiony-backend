<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;
use App\Models\Pharmacist;


class PharmacistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        Pharmacist::create([
            'name' => 'د. أحمد',
            'email' => 'ahmed@pharmacy.com',
            'phone' => '0100001111',
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);

        Pharmacist::create([
            'name' => 'د. فاطمة',
            'email' => 'fatma@pharmacy.com',
            'phone' => '0120002222',
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);
    }
}
