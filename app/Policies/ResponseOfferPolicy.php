<?php

namespace App\Policies;

use App\Models\Pharmacist;
use App\Models\ResponseOffer;
use Illuminate\Support\Facades\Auth;

class ResponseOfferPolicy
{
    /**
     * Get current guard (admins, employees, pharmacists, etc.)
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
     * الصلاحيات العامة: 
     * الشركة تشوف الردود الخاصة بعروضها
     * الصيدلية تشوف ردودها فقط
     */
    public function viewAny($user): bool
    {
        return in_array($this->getGuard(), ['admins', 'employees', 'pharmacists']);
    }

    public function viewOrders($user ,  $warehouse_id): bool
    {
        if($this->getGuard() === "employees")
        {

           return  $user->warehouses()->contains($warehouse_id) || $user->hasRole("company_owner");
  
        }
        return false;
        
    }

    



    public function view($user, ResponseOffer $responseOffer): bool
    {
        $guard = $this->getGuard();

        if ($guard === 'employees' && $user->company_id === $responseOffer->offer->company_id) {
            return true;
        }
        if ($guard === 'pharmacists' && $user->pharmacy_id === $responseOffer->pharmacy_id) {
           
            return true;
        }

        return false;
    }

    /**
     * الإنشاء — فقط الصيدلية
     */
    public function create(Pharmacist $user): bool
    {
       
        return $this->getGuard() === 'pharmacists';
    }

    /**
     * التحديث — فقط الشركة (على ردود عروضها)
     */
    public function update($user, ResponseOffer $responseOffer): bool
    {
        return $this->getGuard() === 'employees'
            && $user->company_id === $responseOffer->offer->company_id;
    }

    /**
     * الحذف — فقط الشركة (ردود عروضها)
     */
    public function delete($user, ResponseOffer $responseOffer): bool
    {
        return $this->getGuard() === 'employees'
            && $user->company_id === $responseOffer->offer->company_id;
    }

    /**
     * الإلغاء — فقط الصيدلية (ردودها)
     */
    public function cancel($user, ResponseOffer $responseOffer): bool
    {
        return $this->getGuard() === 'pharmacists'
            && $user->pharmacy_id === $responseOffer->pharmacy_id
            && $responseOffer->status === 'pending';
    }
}
