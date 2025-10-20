<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Location;

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

        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'employees',
        ]);

        $employeeRole = Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'employees',
        ]);

        ///انشاء الموقع الافتراضي
        $location = Location::create([
            'name' => 'Main Location',
        ]);

        //  إنشاء المخزن الافتراضي


        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-001',
            'company_id' => $company->id,
            'location_id' => $location->id,
            'active' => true,
        ]);



        //  إنشاء الموظف (المدير العام)
        $manager = Employee::create([
            'name' => 'Main Manager',
            'email' => 'manager@company.com',
            'password' => Hash::make('password123'),
            'phone' => '01000000000',
            'address' => 'Cairo',
            'role_id' => $managerRole->id,
            'warehouse_id' => $warehouse->id,
            'company_id' => $company->id,
            'active' => true,
        ]);

        $this->command->info(" Company, warehouse, role, and manager created successfully!");
        $this->command->warn(" Manager Login: manager@company.com / password123");
    }
}
