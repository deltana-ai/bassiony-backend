<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $site_permissions = Permission::where('guard_name','admins')->pluck('name')->toArray();
        $pharmacy_permissions = Permission::where('guard_name','pharmacists')->pluck('name')->toArray();
        $company_permissions = Permission::where('guard_name','employees')->pluck('name')->toArray();

        $superAdmin = Role::firstOrCreate(['name' => 'site_owner','guard_name'=>'admins']);
        $superManger = Role::firstOrCreate(['name' => 'company_owner','guard_name'=>'employees']);
        $superpharmacist = Role::firstOrCreate(['name' => 'pharmacy_owner','guard_name'=>'pharmacists']);
       
        $superAdmin->givePermissionTo($site_permissions);
        $superManger->givePermissionTo($company_permissions);
        $superpharmacist->givePermissionTo($pharmacy_permissions);
    }
}
