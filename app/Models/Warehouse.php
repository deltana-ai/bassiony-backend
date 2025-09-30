<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $guarded = ['id'];
    
    protected $casts = [
        'active' => 'boolean',
    ];

     public function company()
    {
        return $this->belongsTo(Company::class);
    }

     public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'warehouse_product')->withPivot('warehouse_price', 'stock','reserved_stock','expiry_date','batch_number');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_warehouse');
    }

}
