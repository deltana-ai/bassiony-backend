<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRoute extends Model
{
    protected $guarded = ['id'];

      protected $casts = [
        'locations' => 'array',
        'estimated_distance' => 'decimal:2',
        'base_shipping_cost' => 'decimal:2',
        'active' => 'boolean',
    ];

    
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
