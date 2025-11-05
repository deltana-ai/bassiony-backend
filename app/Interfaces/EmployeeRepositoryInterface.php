<?php

namespace App\Interfaces;

use App\Interfaces\Interfaces\ICrudRepository;
use App\Models\Employee;

interface EmployeeRepositoryInterface extends ICrudRepository
{
    public function createEmployee( array $data);
    
    public function updateEmployee(Employee $employee, array $data);

    public function assignToWarehouse(string $tableName, array $ids, int $warehouse_id );

    public function assignToRole(string $tableName, array $ids, int $role_id );

}
