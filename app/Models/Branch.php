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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

   
    public function products()
    {
        return $this->belongsToMany(Product::class, 'branch_product', 'branch_id', 'product_id')
                    ->withPivot([ 'stock', 'reserved_stock', 'expiry_date', 'batch_number'])
                    ->withTimestamps();
    }
    

    


   
      

    
}
