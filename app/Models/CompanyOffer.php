<?php

namespace App\Models;

use App\Policies\CompanyOfferPolicy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyOffer extends BaseModel
{
    use  SoftDeletes;
    protected $guarded = ['id'];


    protected $casts = [
        'start_date' =>'date',
        'end_date' =>'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    // من خلال warehouseProduct نقدر نوصل للمنتج مباشرة
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function responses()
    {
        return $this->hasMany(ResponseOffer::class);
    }


    public function isCurrentlyActive(): bool
    {
        if (!$this->active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->start_date && $today->lt(Carbon::parse($this->start_date))) {
            return false;
        }

        if ($this->end_date && $today->gt(Carbon::parse($this->end_date))) {
            return false;
        }

        return true;
    }

    public static function policy()
    {
        return CompanyOfferPolicy::class;
    }


     /**
     * Get active offer for a product
     */
    public static function getActiveOffer(int $companyId, int $productId , int $quantity): ?CompanyOffer
    {
        return self::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('active', true)
            ->where(function($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::now());
            })
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::now());
            })
            ->where('min_quantity', '<=', $quantity)
            ->first();
    }

    /**
     * Calculate price with offer applied
     */
    // public static function calculateOfferPrice(CompanyOffer $offer, Product $product, int $quantity): array
    // {
    //     $basePrice = $product->price;
    //     $items = [];
    //     $totalPrice = 0;

    //     if ($offer->offer_type === 'DISCOUNT') {
    //         // Simple discount offer
    //         $discountedPrice = $basePrice * (1 - ($offer->discount / 100));
            
    //         $items[] = [
    //             'quantity' => $quantity,
    //             'price' => round($discountedPrice * $quantity, 2),
    //         ];
            
    //         $totalPrice = round($discountedPrice * $quantity, 2);

    //     } elseif ($offer->offer_type === 'BUY_X_GET_Y') {
    //         // Buy X Get Y offer
    //         $minQuantity = $offer->min_quantity ?? 1;
    //         $freeQuantity = $offer->get_free_quantity ?? 0;
    //         $maxRedemption = $offer->max_redemption_per_invoice ?? PHP_INT_MAX;

    //         // Calculate how many complete sets can be redeemed
    //         $completeSets = min(
    //             floor($quantity / $minQuantity),
    //             $maxRedemption
    //         );

    //         // Remaining quantity after offer sets
    //         $remainingQuantity = $quantity - ($completeSets * $minQuantity);
    //         $totalFreeItems = $completeSets * $freeQuantity;

    //         // Add paid items
    //         if ($completeSets * $minQuantity > 0) {
    //             $items[] = [
    //                 'quantity' => $completeSets * $minQuantity,
    //                 'price' => round($basePrice * $completeSets * $minQuantity, 2),
    //             ];
    //             $totalPrice += round($basePrice * $completeSets * $minQuantity, 2);
    //         }

    //         // Add remaining paid items (not part of offer)
    //         if ($remainingQuantity > 0) {
    //             $items[] = [
    //                 'quantity' => $remainingQuantity,
    //                 'price' => round($basePrice * $remainingQuantity, 2),
    //             ];
    //             $totalPrice += round($basePrice * $remainingQuantity, 2);
    //         }

    //         // Add free items (price = 0)
    //         if ($totalFreeItems > 0) {
    //             $items[] = [
    //                 'quantity' => $totalFreeItems,
    //                 'price' => 0,
    //             ];
    //         }
    //     }

    //     return [
    //         'items' => $items,
    //         'total_price' => $totalPrice,
    //     ];
    // }


    public static function calculateOfferPrice(CompanyOffer $offer, Product $product, int $quantity): array
    {
        return match ($offer->offer_type) {
            'DISCOUNT'      => self::calculateDiscount($offer, $product, $quantity),
            'BUY_X_GET_Y'   => self::calculateBuyXGetY($offer, $product, $quantity),
            default         => ['items' => [], 'total_price' => 0],
        };
    }

    
   

    protected static function calculateDiscount(CompanyOffer $offer, Product $product, int $quantity): array
    {
        $price = $product->price * (1 - ($offer->discount / 100));
        $total = round($price * $quantity, 2);

        return [
            'items' => [
                [
                    'quantity' => $quantity,
                    'price'    => $total,
                ]
            ],
            'total_price' => $total,
        ];
    }


     protected static function calculateBuyXGetY(CompanyOffer $offer, Product $product, int $quantity): array
    {
        $basePrice = $product->price;

        $x      = $offer->min_quantity;
        $y      = $offer->get_free_quantity;
        $max    = $offer->max_redemption_per_invoice ?? PHP_INT_MAX;

        $sets           = self::completeSets($quantity, $x, $max);
        $paidQuantity   = self::paidQuantity($sets, $x);
        $freeQuantity   = self::freeQuantity($sets, $y);
        $remaining      = $quantity - $paidQuantity;

        $items = [];
        $totalPrice = 0;

        // Paid (offer part)
        if ($paidQuantity > 0) {
            $price = $paidQuantity * $basePrice;
            $items[] = [
                'quantity' => $paidQuantity,
                'price'    => round($price, 2),
            ];
            $totalPrice += round($price, 2);
        }

        // Paid (extra items)
        if ($remaining > 0) {
            $price = $remaining * $basePrice;
            $items[] = [
                'quantity' => $remaining,
                'price'    => round($price, 2),
            ];
            $totalPrice += round($price, 2);
        }

        // Free
        if ($freeQuantity > 0) {
            $items[] = [
                'quantity' => $freeQuantity,
                'price'    => 0,
            ];
        }

        return [
            'items' => $items,
            'total_price' => $totalPrice,
        ];
    }


    protected static function completeSets(int $quantity, int $minQuantity, int $max): int
    {
        return min(floor($quantity / $minQuantity), $max);
    }

    protected static function paidQuantity(int $sets, int $minQuantity): int
    {
        return $sets * $minQuantity;
    }

    protected static function freeQuantity(int $sets, int $freeQuantity): int
    {
        return $sets * $freeQuantity;
    }

}
