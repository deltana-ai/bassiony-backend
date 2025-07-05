<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends BaseModel
{
    use HasMedia;

    protected $with = [
        'media',
    ];

    protected $guarded = ['id'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'pharmacy_products')
            ->withPivot('id', 'price', 'quantity')
            ->withTimestamps();
    }

    public function pharmacyProducts()
    {
        return $this->hasMany(PharmacyProduct::class);
    }
}
