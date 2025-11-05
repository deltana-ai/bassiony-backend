<?php

namespace App\Repositories;

use App\Helpers\Constants;
use App\Interfaces\BranchRepositoryInterface;
use App\Models\Branch;
use App\Models\BranchProductBatch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BranchRepository extends CrudRepository implements BranchRepositoryInterface
{
    protected Model $model;

    public function __construct(Branch $model)
    {
        $this->model = $model;
    }

     /**
     * Get all products in branch with summary only
     */
    public function getBranchProducts(int $branchId)
    {
       
        $filters = request(Constants::FILTERS) ?? [];
        $per_page = request(Constants::PER_PAGE) ?? 15;
        $paginate = request(Constants::PAGINATE) ?? true;
        $query = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.active',
                'branch_product.reserved_stock',
                DB::raw('COALESCE(SUM(branch_product_batches.stock), 0) as total_stock'),
                DB::raw('COUNT(DISTINCT branch_product_batches.id) as total_batches')
            ])
            ->join('branch_product', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'branch_product.product_id')
                     ->where('branch_product.branch_id', '=', $branchId);
            })
            ->leftJoin('branch_product_batches', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'branch_product_batches.product_id')
                     ->where('branch_product_batches.branch_id', '=', $branchId);
            })
            ->groupBy([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.active',
                'branch_product.reserved_stock'
            ]);
       

        

        $products = $query->paginate($per_page);

        return $products;


        
    }


     /**
     * Get batch details for specific product in branch
     */
    public function getProductBatches(int $productId, int $branchId)
    {
        // Verify product exists in branch
        $exists = DB::table('branch_product')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->exists();

        if (!$exists) {
            throw new \Exception('Product not found in this branch');
        }

        $batches = BranchProductBatch::with(['product:id,name','branch:id,name'])        // DB::table('branch_product_batches')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->orderBy('expiry_date', 'asc')
            ->orderBy('created_at', 'desc')
            
            ->get();

        return $batches;

       
    }


    /**
     * Get batch status label
     */
    protected function getBatchStatusLabel(string $status, ?int $daysUntilExpiry, bool $isExpired): string
    {
        if ($isExpired) {
            return 'Expired';
        }

        if ($status === 'expiring_soon' && $daysUntilExpiry !== null) {
            return "Expires in {$daysUntilExpiry} days";
        }

        if ($daysUntilExpiry !== null) {
            return "Valid for {$daysUntilExpiry} days";
        }

        return 'No expiry';
    }


    /**
     * Get stock status label
     */
    protected function getStockStatus(int $availableStock): string
    {
        if ($availableStock <= 0) {
            return 'out_of_stock';
        } elseif ($availableStock <= 10) {
            return 'low_stock';
        } elseif ($availableStock <= 50) {
            return 'medium_stock';
        } else {
            return 'in_stock';
        }
    }
}
