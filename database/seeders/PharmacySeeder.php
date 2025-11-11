<?php 



// database/seeders/PharmacyProductSeeder.php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Pharmacist;
use App\Models\Pharmacy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pharmacies')->insert([
            [
                'name'        => 'Pharmacy 1',
                'address'    => 'Location 1',
                'phone'       => '123456789',
            ],
            [
                'name'        => 'Pharmacy 2',
                'address'    => 'Location 2',
                'phone'       => '987654321',
            ],
        ]);

        DB::table('branches')->insert([
            [
                'name'        => 'Pharmacy 1',
                'address'    => 'Location 1',
                'pharmacy_id'    => Pharmacy::first()->id,
                'active'       => 1,
            ],
            [
                'name'        => 'Pharmacy 2',
                'address'    => 'Location 2',
                'pharmacy_id'    => Pharmacy::first()->id ,

                'active'       => 1,
            ],
        ]);

         //  إنشاء الموظف (المدير العام)
        $manager = Pharmacist::create([
            'name' => 'Main Manager',
            'email' => 'manager@pharmacy.com',
            'password' => Hash::make('passwordPharmacy123'),
            'phone' => '01000000001',
            'pharmacy_id' => Pharmacy::first()->id,
            'is_owner' => 1,
            'branch_id' => Branch::first()->id,
        ]);
        $pharmacy_permissions = Permission::where('guard_name','pharmacists')->pluck('name')->toArray();
        $superpharmacist = Role::firstOrCreate(['name' => 'pharmacy_owner','guard_name'=>'pharmacists','pharmacy_id'=>Pharmacy::first()->id]);
        $superpharmacist->givePermissionTo($pharmacy_permissions);

        $manager->assignRole('pharmacy_owner');
    }
}

      
