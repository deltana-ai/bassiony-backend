<?php

namespace App\Policies;

use App\Models\Employee;

class EmployeePolicy
{
    /**
     * Create a new policy instance.
     */
    public function manage( Employee $employee)
    {
        return  $employee->company_id === auth()->guard("employees")->user()->company_id;
    }
}
