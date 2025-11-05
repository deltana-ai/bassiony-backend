<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchProduct extends BaseModel
{
    protected $table = 'branch_product'; 
    protected $guarded = ['id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    
}
