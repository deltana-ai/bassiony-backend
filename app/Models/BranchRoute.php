<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchRoute extends Model
{
     protected $guarded = ['id'];

     protected $casts = [
        'locations' => 'array',
        'estimated_distance' => 'decimal:2',
        'base_shipping_cost' => 'decimal:2',
        'active' => 'boolean',
    ];

    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
