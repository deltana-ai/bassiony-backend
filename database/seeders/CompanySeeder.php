<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Employee;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء الشركة الأساسية
        $company = Company::create([
            'name' => 'Main Company',
            'address' => 'Cairo, Egypt',
            'phone' => '01000000000',
        ]);

        
        
       // $user->assignRole('employee');


      

        ///انشاء الموقع الافتراضي
      
        //  إنشاء المخزن الافتراضي


        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'company_id' => $company->id,
            'location' => "location1",
            'active' => true,
        ]);



        //  إنشاء الموظف (المدير العام)
        $manager = Employee::create([
            'name' => 'Main Manager',
            'email' => 'manager@company.com',
            'password' => Hash::make('password123'),
            'phone' => '01000000000',
            'address' => 'Cairo',
            'warehouse_id' => $warehouse->id,
            'company_id' => $company->id,
            'active' => true,
        ]);
        $manager->assignRole('company_owner');

        $this->command->info(" Company, warehouse, role, and manager created successfully!");
        $this->command->warn(" Manager Login: manager@company.com / password123");
    }
}
