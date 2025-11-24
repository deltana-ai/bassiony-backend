<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Employee;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
            'company_id' => $company->id,
            'is_owner' => true,
            'active' => true,
        ]);
        $superManger = Role::firstOrCreate(['name' => 'company_owner_'.$company->id,'guard_name'=>'employees',"company_id"=>$company->id]);
        $company_permissions = Permission::where('guard_name','employees')->pluck('name')->toArray();
        $superManger->givePermissionTo($company_permissions);

        $manager->assignRole($superManger);

        $this->command->info(" Company, warehouse, role, and manager created successfully!");
        $this->command->warn(" Manager Login: manager@company.com / password123");
    }
}
