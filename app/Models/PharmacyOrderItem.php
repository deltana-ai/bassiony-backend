<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyOrderItem extends Model
{
    protected $guarded = ['id'];


    public function order()
    {
        return $this->belongsTo(PharmacyOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
