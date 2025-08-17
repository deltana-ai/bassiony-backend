<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use HasMedia , SoftDeletes;

    protected $with = [
        'media',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
        'show_home' => 'boolean'
    ];

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_product');
    }

    public function ratings()
    {
            return $this->hasMany(ProductRating::class);
    }

public function pharmacies()
{
    return $this->belongsToMany(Pharmacy::class, 'pharmacy_products')
                ->withPivot('price', 'stock', 'expiry_date')
                ->withTimestamps();
}
}
