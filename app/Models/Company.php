<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends BaseModel
{
    use HasMedia, SoftDeletes;
    protected $guarded = ['id'];

    protected $with = [
        'media',
    ];

    public function products()
    {
        return $this->hasMany(CompanyProduct::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
   

}
