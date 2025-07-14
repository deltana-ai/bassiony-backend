<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class PharmacyProduct extends BaseModel
{
    use HasMedia;

    protected $with = [
        'media',
    ];

    protected $guarded = ['id'];

    protected $table = 'pharmacy_products';

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function offer()
    {
        return $this->hasOne(Offer::class);
    }

    /////////////make by zeinab///////////////////////
    public function priceAfterOffer()
    {
        if ($this->offer && $this->offer->isValid()) {
          return $this->price - $this->offer->discount_price;
        }
        return $this->price;

    }
    //////////////////////////////////////////////////
}
