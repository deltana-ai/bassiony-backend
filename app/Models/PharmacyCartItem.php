<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyCartItem extends Model
{
    protected $guarded = ['id'];

     protected $casts = [
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(PharmacyCart::class, 'cart_id');
    }

    public function offer()
    {
        return CompanyOffer::getActiveOffer($this->cart->company_id, $this->product_id, $this->quantity);

    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get item total price
     */
    public function getTotalPriceAttribute(): float
    {
        $price = CompanyOffer::calculateOfferPrice($this->offer, $this->product, $this->quantity)['total_price'];
        return $price;
    }

    public function getUnitPriceAttribute(): float
    {
        return $this->product->price - $this->discount_percent;
    }

    public function getFreeQuantityAttribute(): int
    {
        if($this->offer->offer_type === 'BUY_X_GET_Y') {

            $max  = $offer->max_redemption_per_invoice ?? PHP_INT_MAX;

            $sets = CompanyOffer::completeSets($this->quantity,  $this->offer->min_quantity,  $max);

            return $sets * $this->offer->get_free_quantity;
        }
        return  0;
    }

    public function getDiscountPercentAttribute(): int
    {
        return $this->offer? $this->offer->discount_percent??$this->product->companyPrice?->discount_percent:0;

    }

}
