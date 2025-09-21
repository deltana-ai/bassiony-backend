<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProduct extends Model
{
    protected $guarded = ['id'];

      protected $casts = [
        'wholesale_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

     public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    public function getAvailableStockAttribute()
    {
        return max(0, $this->stock - $this->reserved_stock);
    }
}
