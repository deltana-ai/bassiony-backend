<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyProduct extends Model
{
    use Media, SoftDeletes;

     protected $with = [
        'media',
    ];

    protected $guarded = ['id'];


        public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    // علاقة مع Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
