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

        $superAdmin = Role::firstOrCreate(['name' => 'site_owner','guard_name'=>'admins']);
       
        $superAdmin->givePermissionTo($site_permissions);
    }
}
