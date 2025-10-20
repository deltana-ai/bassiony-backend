<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use HasMedia , SoftDeletes;

    protected $with = [
        'media','offers','pharmacies',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }


    public function pharmacies()
    {
        return $this->belongsToMany(Pharmacy::class, 'pharmacy_product')
                    ->withPivot('price', 'stock','reserved_stock')
                    ->withTimestamps();
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_product')
                    ->withPivot('branch_price', 'stock','reserved_stock','expiry_date','batch_number')
                    ->withTimestamps();
    }



    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_product') ->withPivot( 'stock','reserved_stock','expiry_date','batch_number')
                    ->withTimestamps();;
    }
}
