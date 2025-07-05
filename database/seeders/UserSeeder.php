<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        User::create([
            'name' => 'محمد أحمد',
            'email' => 'mohamed@example.com',
            'phone' => '01012345678',
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);

        User::create([
            'name' => 'خالد حسن',
            'email' => 'khaled@example.com',
            'phone' => '01198765432',
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);
    }
}
