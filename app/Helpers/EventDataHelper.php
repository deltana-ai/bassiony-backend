<?php

namespace App\Helpers;

use App\Models\Conference;
use App\Models\Order;
use App\Models\User;

class EventDataHelper
{
    /**
     * @param int $id
     * @return void
     */


  

    /**
     * Get the price based on type and user ID.
     * @param float $totalPrice
     * @param float $discountValue
     * @param string $discountType
     * @return float The price.
     */
    public static function applyDiscount(float $totalPrice, float $discountValue, string $discountType): float
    {
        if ($discountType === 'percentage') {
            $discountedPrice = $totalPrice * ($discountValue / 100);
        } else {
            $discountedPrice = $discountValue;
        }
        return $discountedPrice; // Ensure the price doesn't go below zero
    }
}
