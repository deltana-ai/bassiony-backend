<?php

namespace App\Repositories;

use App\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class EmployeeRepository extends CrudRepository implements EmployeeRepositoryInterface
{
    protected Model $model;

    public function __construct(Employee $model)
    {
        $this->model = $model;
    }
    public function createEmployee( array $data)
    {
       return DB::transaction(function () use ($data) {

            $role = Role::find($data["role_id"]);
            unset($data["role_id"]);
            $employee = $this->create($data);
            $employee ->assignRole($role);
            return $employee;


       });
    }

    public function updateEmployee(Employee $employee, array $data)
    {
       return DB::transaction(function () use ($data , $employee) {

            $role = Role::find($data["role_id"]);
            unset($data["role_id"]);
            $employee->update($data);
            $employee->syncRoles($role);
            return $employee;


       });
    }


    public function assignToWarehouse(string $tableName, array $ids, int $warehouse_id )
    {
        DB::table($tableName)->whereIn('id', $ids)->update(["warehouse_id" => $warehouse_id]);

    }
    public function assignToRole(string $tableName, array $ids, int $role_id )
    {
        $role = Role::find($role_id);

        $employees = DB::table($tableName)->whereIn('id', $ids)->get();

        foreach ($employees as $employee) {

            if($employee->guard_name === 'company_owner' || $employee->company_id === auth()->user()->company_id);
            {
               $employee->syncRoles($role);
            }
            
        }
        

    }
}

