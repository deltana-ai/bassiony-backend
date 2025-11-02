<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Policies\CompanyPolicy;

class Company extends BaseModel
{
    use HasMedia, SoftDeletes;
    protected $guarded = ['id'];

    protected $with = [
        'media',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public static function policy()
    {
        return CompanyPolicy::class;
    }

   

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
   
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

}
