<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = ['id'];
    
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

}
