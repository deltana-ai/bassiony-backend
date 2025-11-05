<?php

namespace App\Repositories;

use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constants;
use App\Models\Product;
use App\Models\WarehouseProductBatch;

class WarehouseRepository extends CrudRepository implements WarehouseRepositoryInterface
{
    protected Model $model;

    public function __construct(Warehouse $model)
    {
        $this->model = $model;
    }


    /**
     * Get all products in warehouse with summary only
     */
    public function getWarehouseProducts(int $warehouseId)
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
                'warehouse_product.reserved_stock',
                DB::raw('COALESCE(SUM(warehouse_product_batches.stock), 0) as total_stock'),
                DB::raw('COUNT(DISTINCT warehouse_product_batches.id) as total_batches')
            ])
            ->join('warehouse_product', function ($join) use ($warehouseId) {
                $join->on('products.id', '=', 'warehouse_product.product_id')
                     ->where('warehouse_product.warehouse_id', '=', $warehouseId);
            })
            ->leftJoin('warehouse_product_batches', function ($join) use ($warehouseId) {
                $join->on('products.id', '=', 'warehouse_product_batches.product_id')
                     ->where('warehouse_product_batches.warehouse_id', '=', $warehouseId);
            })
            ->groupBy([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.active',
                'warehouse_product.reserved_stock'
            ]);
       

        // foreach ($filters as $key => $value) {
        //     if (is_numeric($value)) {
        //         $query = $query->where("products.".$key, '=', $value);
        //     } else {
        //         $query = $query->where("products.".$key, 'LIKE', '%' . $value . '%');
        //     }
        // }

        $products = $query->paginate($per_page);

        return $products;


        
    }


     /**
     * Get batch details for specific product in warehouse
     */
    public function getProductBatches(int $productId, int $warehouseId)
    {
        // Verify product exists in warehouse
        $exists = DB::table('warehouse_product')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->exists();

        if (!$exists) {
            throw new \Exception('Product not found in this warehouse');
        }

        $batches = WarehouseProductBatch::with(['product:id,name','warehouse:id,name'])        // DB::table('warehouse_product_batches')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
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
