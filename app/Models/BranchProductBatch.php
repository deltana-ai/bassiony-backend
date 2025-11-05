<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchProductBatch extends Model
{
    protected $guarded = ['id'];

    
     protected $casts = [
        'expiry_date' => 'date',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

}
