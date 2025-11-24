<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        DB::table('admins')->insert(
            [
                [
                    'name' => 'Adam Smith',
                    'email' => 'adam@wsa-network.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'super_admin' => true,
                    'created_at' => now(),
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Fred Tripoli',
                    'email' => 'fred@wsa-network.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'super_admin' => true,
                    'created_at' => now(),
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Remon Sarofeem',
                    'email' => 'remon@wsa-network.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'super_admin' => false,
                    'created_at' => now(),
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Summer',
                    'email' => 'summer@wsa-network.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'super_admin' => false,
                    'created_at' => now(),
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Emily',
                    'email' => 'emily@wsa-network.com',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'super_admin' => false,
                    'created_at' => now(),
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ],
            ]
        );
        $site_permissions = Permission::where('guard_name','admins')->pluck('name')->toArray();

        $superAdmin = Role::firstOrCreate(['name' => 'site_owner','guard_name'=>'admins']);
        $super_admins = Admin::where('super_admin',1)->get();
        $superAdmin->givePermissionTo($site_permissions);

        foreach ($super_admins as  $super_admin) {
            $super_admin->assignRole($superAdmin);
        }
    }
}
