<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyCart extends Model
{
    
    protected $guarded = ['id'];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PharmacyCartItem::class, 'cart_id');
    }

    /**
     * Get total items count in cart
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity') + $this->items->sum('free_quantity');
    }

    

    

    

    /**
     * Get cart total price (without offers)
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->items->sum('total_price');
    }
}
