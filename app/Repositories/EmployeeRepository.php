<?php

namespace App\Repositories;

use App\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmployeeRepository extends CrudRepository implements EmployeeRepositoryInterface
{
    protected Model $model;

    public function __construct(Employee $model)
    {
        $this->model = $model;
    }


    public function assignToWarehouse(string $tableName, array $ids, int $warehouse_id )
    {
        DB::table($tableName)->whereIn('id', $ids)->update(["warehouse_id" => $warehouse_id]);

    }
    public function assignToRole(string $tableName, array $ids, int $role_id )
    {
        DB::table($tableName)->whereIn('id', $ids)->update(["role_id" => $role_id]);

    }
}

