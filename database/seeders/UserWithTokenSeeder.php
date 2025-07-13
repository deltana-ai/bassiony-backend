<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\{User,Pharmacist,CompanyManager,Owner,Driver};
class UserWithTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
          'name' =>'client name',
          'email' =>'clientemail@gmail.com',
          'firebase_uid' => "123456789",
          'is_verified' => true,

        ]);
        $user->tokens()->delete();
        $usertoken = $user->createToken('client-token')->plainTextToken();

        $pharmacist = Pharmacist::firstOrCreate([
          'name' =>'pharmacist name',
          'email' =>'pharmacistemail@gmail.com',
          'firebase_uid' => "123456785555",

          'password' => Hash::make('pharmacist123'),
          'is_verified' => true,

        ]);
        $pharmacist->tokens()->delete();
        $pharmacistoken = $pharmacist->createToken('pharmacist-token')->plainTextToken();
        $user->firebase_uid =$usertoken;
        $user->save();
        $pharmacist->firebase_uid =$pharmacistoken;
        $pharmacist->save();

    }
}
