<?php

namespace App\Repositories;

use App\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use App\Models\Warehouse;
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
            if (!$role) {

                throw new \Exception("الدور الوظيفي غير موجود");
            }
            $warehouses = $data["warehouses"];
            unset($data["role_id"]);
            unset($data["warehouses"]);
            $employee = $this->create($data);
              
            $employee ->assignRole($role);
           
            $employee ->warehouses()->sync($warehouses);
           
            return $employee;


       });
    }

    public function updateEmployee(Employee $employee, array $data)
    {
       return DB::transaction(function () use ($data , $employee) {

            $role = Role::find($data["role_id"]);
            $warehouses = $data["warehouses"];
            unset($data["role_id"]);
            unset($data["warehouses"]);
            $employee->update($data);
            $employee->syncRoles($role);
            $employee ->warehouses()->sync($warehouses);
            return $employee;


       });
    }


    public function assignToWarehouse( array $ids, int $warehouse_id )
    {
        $warehouse = Warehouse::find($warehouse_id);
        $warehouse->employees()->syncWithoutDetaching($ids);

    }
    public function assignToRole(string $tableName, array $ids, int $role_id )
    {
        $role = Role::find($role_id);

        $employees = $this->model->whereIn('id', $ids)->get();

        foreach ($employees as $employee) {

            if($employee->guard_name === 'company_owner' || $employee->company_id === auth()->user()->company_id);
            {
               $employee->syncRoles($role);
            }
            
        }
        

    }
}

