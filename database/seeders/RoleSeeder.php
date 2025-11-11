<?php

namespace Database\Seeders;

use App\Models\Admin;
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
        $super_admins = Admin::where('super_admin',1)->get();
        foreach ($super_admins as  $super_admin) {
            $super_admin->assignRole('site_owner');
        }
        $superAdmin->givePermissionTo($site_permissions);
    }
}
