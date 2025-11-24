<?php

namespace App\Models;

use App\Policies\CompanyOfferPolicy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyOffer extends BaseModel
{
    use  SoftDeletes;
    protected $guarded = ['id'];


    protected $casts = [
        'start_date' =>'date',
        'end_date' =>'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouseProduct()
    {
        return $this->belongsTo(WarehouseProduct::class);
    }

    // من خلال warehouseProduct نقدر نوصل للمنتج مباشرة
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            WarehouseProduct::class,
            'id',                  // warehouse_product.id
            'id',                  // product.id
            'warehouse_product_id', // company_offers.warehouse_product_id
            'product_id'           // warehouse_product.product_id
        );
    }

    public function responses()
    {
        return $this->hasMany(ResponseOffer::class);
    }


    public function isCurrentlyActive(): bool
    {
        if (!$this->active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->start_date && $today->lt(Carbon::parse($this->start_date))) {
            return false;
        }

        if ($this->end_date && $today->gt(Carbon::parse($this->end_date))) {
            return false;
        }

        return true;
    }

    public static function policy()
    {
        return CompanyOfferPolicy::class;
    }

}
