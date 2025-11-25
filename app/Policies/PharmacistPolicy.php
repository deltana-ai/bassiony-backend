<?php

namespace App\Policies;

use App\Models\Pharmacist;
use App\Models\User;

class PharmacistPolicy
{
    /**
     * Create a new policy instance.
     */
    public function manage( Pharmacist $pharmacist)
    {
        return $pharmacist->pharmacy_id === auth()->guard("pharmacists")->user()->pharmacy_id;
    }
}
