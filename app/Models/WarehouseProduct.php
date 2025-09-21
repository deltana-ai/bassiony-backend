<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends Model
{
    protected $guarded = ['id'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function companyProduct()
    {
        return $this->belongsTo(CompanyProduct::class);
    }

    

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereDate('expiry_date', '<=', now()->addDays($days));
    }
    public function getAvailableStockAttribute()
    {
        return max(0, $this->stock - $this->reserved_stock);
    }
}
