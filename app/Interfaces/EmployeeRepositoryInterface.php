<?php

namespace App\Interfaces;

use App\Interfaces\Interfaces\ICrudRepository;

interface EmployeeRepositoryInterface extends ICrudRepository
{
    
    public function assignToWarehouse(string $tableName, array $ids, int $warehouse_id );

    public function assignToRole(string $tableName, array $ids, int $role_id );

}
