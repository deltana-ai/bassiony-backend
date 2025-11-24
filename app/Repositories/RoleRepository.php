<?php

namespace App\Repositories;

use App\Interfaces\RoleRepositoryInterface;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RoleRepository extends CrudRepository implements RoleRepositoryInterface
{
    protected Model $model;

    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    public function createRole(array $data)
    {
        $permissions = $data["permissions"];
        $userEntrprice = null;
        
        $data_create = ['name' => $data["name"]  ,'guard_name'=> auth()->user()->guard_name];
        if (auth()->guard("employees")->check()) {
            $userEntrprice = auth()->guard("employees")->user()->company_id;
            $data_create["company_id"]  =  $userEntrprice;
            $data_create["name"]  = $data["name"]."_".$userEntrprice;
        }
        if (auth()->guard("pharmacists")->check()) {
           $userEntrprice =  auth()->guard("pharmacists")->user()->pharmacy_id;
           $data_create["pharmacy_id"]  =  $userEntrprice;

           $data_create["name"]  = $data["name"] ."_". $userEntrprice;
        }
        $role = Role::create($data_create);
       
        $permissions = Permission::whereIn('id', $permissions)->get(['name'])->toArray();
    
        $role->syncPermissions($permissions);
        return $role;
    }

    public function updateRole($role, array $data)
    {
        $permissions = $data["permissions"];
        
        $permissions = Permission::whereIn('id', $permissions)->get(['name'])->toArray();
        $roleEntrprise = $role->pharmacy_id ?? $role->company_id ?? null;
        $role->update(['name' => $data["name"]."_".$roleEntrprise]);

        $role->syncPermissions($permissions);

        return $role;
    }

    public function getPermissions()
    {
        return Permission::where('guard_name',auth()->user()->guard_name)->get();
    }

    public function deleteRoles( array $roleIds ,$user_model ) 
    {
        DB::transaction(function () use ($roleIds,$user_model) {
            $roles = Role::whereIn('id', $roleIds)->get();
            foreach ($roles as $role) {
                
                if ($role->guard_name !== auth()->user()->guard_name  ) {
                   continue;
                }
                if ($role->guard_name ==="employees" && $role->company_id !== auth()->user()->company_id) {
                    continue;
                }
                if ($role->guard_name ==="pharmacists" && $role->pharmacy_id !== auth()->user()->pharmacy_id) {
                    continue;
                }
                
                if (in_array($role->name, ['company_owner', 'pharmacy_owner', 'site_owner'])) {
                    continue;
                }
                
                $usersCount = DB::table('model_has_roles')->where('role_id', $role->id)->where('model_type', $user_model)->count();
                if ($usersCount > 0 ) {
                    continue;
                }
                $role->permissions()->detach();
                $role->delete();
            }
        });
    }
}

