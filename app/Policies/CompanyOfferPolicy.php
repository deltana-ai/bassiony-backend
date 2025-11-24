<?php

namespace App\Policies;

use App\Models\CompanyOffer;
use Illuminate\Support\Facades\Auth;

class CompanyOfferPolicy
{
    /**
     * Get the current active guard.
     */
    protected function getGuard(): ?string
    {
        foreach (array_keys(config('auth.guards')) as $key) {
            if (Auth::guard($key)->check()) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Determine whether the user can view any offers.
     * (Admins, employees, and pharmacists can view)
     */
    public function viewAny($user): bool
    {
        return in_array($this->getGuard(), ['admins', 'employees', 'pharmacists']);
    }

    /**
     * Determine whether the user can view a specific offer.
     */
    public function view($user, CompanyOffer $companyOffer): bool
    {
        return in_array($this->getGuard(), ['admins', 'employees', 'pharmacists']);
    }

    /**
     * Determine whether the user can create offers.
     * (Only employees of a company)
     */
    public function create($user): bool
    {
        return $this->getGuard() === 'employees';
    }

    /**
     * Determine whether the user can update offers.
     * (Only employees belonging to the same company)
     */
    public function update($user, CompanyOffer $companyOffer): bool
    {
        return $this->getGuard() === 'employees';
           // && $user->company_id === $companyOffer->company_id;
    }

    /**
     * Determine whether the user can delete offers.
     */
    public function delete($user, CompanyOffer $companyOffer): bool
    {
        return $this->getGuard() === 'employees';
           // && $user->company_id === $companyOffer->company_id;
    }
}
