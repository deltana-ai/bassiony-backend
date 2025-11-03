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
            'id', // foreign key on warehouse_product
            'id', // foreign key on products
            'warehouse_product_id', // local key on company_offers
            'product_id' // local key on warehouse_product
        );
    }

    public function responses()
    {
        return $this->hasMany(ResponseOffer::class);
    }


    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
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
