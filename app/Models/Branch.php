<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
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

    public function branchProducts()
    {
       return $this->hasMany(BranchProduct::class);
    }
      

    
}
