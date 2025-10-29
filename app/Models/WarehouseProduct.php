<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends BaseModel
{
    protected $table = 'warehouse_product'; 
    protected $guarded = ['id'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function offers()
    {
        return $this->hasMany(CompanyOffer::class);
    }
}
