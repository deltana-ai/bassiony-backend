<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchProduct extends Model
{
    protected $guarded = ['id'];

    

    protected $casts = [
        'expiry_date' => 'date',
        'branch_price' => 'decimal:2',
    ];

     public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    
    public function warehouseProduct()
    {
        return $this->belongsTo(WarehouseProduct::class);
    }
    public function getAvailableStockAttribute()
    {
        return max(0, $this->stock - $this->reserved_stock);
    }
}
