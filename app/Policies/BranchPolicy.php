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
    protected $manger_role = "pharmacy_owner";
    
    public function manage( Pharmacist $user, Branch $branch)
    {
        
        return  $user->pharmacy_id === $branch->id || $user->getRoleNames()->first() === $this->manger_role;

    }
}
