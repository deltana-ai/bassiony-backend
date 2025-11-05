<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProductBatch extends Model
{
     protected $guarded = ['id'];

    
     protected $casts = [
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
