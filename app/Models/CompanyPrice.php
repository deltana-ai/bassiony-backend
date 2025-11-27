<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPrice extends BaseModel
{

    protected $guarded = ['id'];


     public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function getCompanySellPriceAttribute()
    {
        $price = $this->product->price;

        if ($this->discount_percent) {
            return $price - ($price * $this->discount_percent / 100);
        }

        return $price;
    }


}
