<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
