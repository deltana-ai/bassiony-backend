<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class CompanyPolicy
{

    protected function getGuard()
    {
        foreach (array_keys(config('auth.guards')) as $key) {
            if (Auth::guard($key)->check()) {
                return $key;
            }
        }
        return null;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny( $user): bool
    {
        $guard = $this->getGuard();

        return in_array($guard, ['admins', 'employees', 'pharmacists']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view( $user, Company $company): bool
    {
        $guard = $this->getGuard();

        if ($guard === 'admins') return true;

        if ($guard === 'employees') return true;
        

        if ($guard === 'pharmacists') return true;
        

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create( $user): bool
    {
        return $this->getGuard() === 'admins';

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update( $user, Company $company): bool
    {
        $guard = $this->getGuard();

        if ($guard === 'admins') {
            return true;
        }

        if ($guard === 'employees' && $user->company_id === $company->id ) { 
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete( $user ): bool
    {
        return $this->getGuard() === 'admins';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore( $user): bool
    {
        return $this->getGuard() === 'admins';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete( $user): bool
    {
        return $this->getGuard() === 'admins';
    }
}
