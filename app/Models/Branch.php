<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Branch extends BaseModel
{
     use  SoftDeletes;
    protected $guarded = ['id'];

    
     protected $casts = [
        'active' => 'boolean',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

   

    

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'branch_warehouse');
    }

    public function products()
    {
        return $this->hasMany(BranchProduct::class);
    }
      

    
}
