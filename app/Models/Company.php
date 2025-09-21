<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
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
