<?php

namespace App\Models;

use App\Policies\BranchPolicy;
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

    public static function policy()
    {
        return BranchPolicy::class;
    }

   
    public function products()
    {
        return $this->belongsToMany(Product::class, 'branch_product', 'branch_id', 'product_id')
                    ->withPivot(['reserved_stock'])
                    ->withTimestamps();
    }


    public function batches()
    {
        return $this->hasMany(BranchProductBatch::class);
    }

 

    


     public function getProductStock(int $productId): int
    {
        return $this->batches()
            ->where('product_id', $productId)
            ->sum('stock');
    }


    /**
     * Get stock for all products in this warehouse
     */
    public function getAllProductsStock(): array
    {
        return $this->batches()
            ->selectRaw('product_id, SUM(stock) as total_stock')
            ->groupBy('product_id')
            ->get()
            ->pluck('total_stock', 'product_id')
            ->toArray();
    }
    
      /**
     * Get detailed stock information with product details
     */
    public function getDetailedStock()
    {
        return $this->batches()
            ->selectRaw('product_id, SUM(stock) as total_stock, COUNT(*) as batch_count')
            ->with('product:id,name,price')
            ->groupBy('product_id')
            ->get();
    }
    

    


   
      

    
}
