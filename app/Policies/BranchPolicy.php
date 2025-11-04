<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Pharmacist;
use App\Models\User;

class BranchPolicy
{
    /**
     * Create a new policy instance.
     */
    public function manage( Pharmacist $user, Branch $branch)
    {
        return  $user->branch_id === $branch->id;
    }
}
