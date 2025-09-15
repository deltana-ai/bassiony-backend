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

    public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProduct::class);
    }

}
