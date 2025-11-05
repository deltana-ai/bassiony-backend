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

    public function warehouseBatches()
    {
        return $this->hasMany(WarehouseProductBatch::class);
    }



    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_product') ->withPivot( 'reserved_stock')
                    ->withTimestamps();;
    }

    public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProduct::class);
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
                    ->withPivot('reserved_stock')
                    ->withTimestamps();
    }

    public function branchPatches()
    {
        return $this->hasMany(WarehouseProduct::class);
    }


    public function getTotalStockInWarehouse(int $warehouseId): int
    {
        return $this->warehouseBatches()
            ->where('warehouse_id', $warehouseId)
            ->sum('stock');
    }

    public function getTotalStockInBranch(int $branch_id): int
    {
        return $this->branchPatches()
            ->where('branch_id', $branch_id)
            ->sum('stock');
    }
   
}
