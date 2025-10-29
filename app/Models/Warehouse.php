<?php

namespace App\Models;

use App\Policies\WarehousePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

     public static function policy()
    {
        return WarehousePolicy::class;
    }


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
        return $this->belongsToMany(Product::class, 'warehouse_product')->withPivot( 'stock','reserved_stock','expiry_date','batch_number')->withTimestamps();
    }



}
