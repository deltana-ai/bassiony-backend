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
        $perPage = request(Constants::PER_PAGE) ?? 15;
        $paginate = request(Constants::PAGINATE) ?? true;
        $sortOrder = request(Constants::ORDER_By_DIRECTION) ?? "asc";
        $sortBy = request(Constants::ORDER_BY) ?? "products.id";

         $query = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.active',
                'products.show_home',
                'warehouse_product.reserved_stock',
                DB::raw('COALESCE(SUM(warehouse_product_batches.stock), 0) as total_stock'),
                DB::raw('COUNT(DISTINCT warehouse_product_batches.id) as total_batches')
            ])
           // ->with(['media']) // Load images/media
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
                'products.show_home',
                'warehouse_product.reserved_stock'
            ]);

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        
        if ($paginate) {
            $products = $query->paginate($perPage);
            
            
            
            return $products;
        } else {
            $products = $query->get();
            return $products;

        }
        
    }


     /**
     * Get batch details for specific product in warehouse
     */
    public function getProductBatches(int $productId, int $warehouseId)
    {

        $filters = request(Constants::FILTERS) ?? [];
        $sortOrder = request(Constants::ORDER_By_DIRECTION) ?? "asc";
        $sortBy = request(Constants::ORDER_BY) ?? "id";
        // Verify product exists in warehouse
        $exists = DB::table('warehouse_product')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->exists();

        if (!$exists) {
            throw new \Exception('Product not found in this warehouse');
        }

      

        $query = WarehouseProductBatch::query()
            ->with([
                'product:id,name,bar_code,price',
                'product.category:id,name',
                'warehouse:id,name,location'
            ])
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        // Apply filters
        $query = $this->applyBatchFilters($query, $filters);

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        if ($sortBy !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }

        $batches = $query->get();

        return $batches;

       
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if (empty($value) && $value !== '0' && $value !== 0) {
                continue;
            }

            switch ($key) {
                // Text search (product name or description)
                case 'search':
                    $query->where(function ($q) use ($value) {
                        $q->where('products.name', 'LIKE', '%' . $value . '%')
                          ->orWhere('products.description', 'LIKE', '%' . $value . '%');
                    });
                    break;

                

                case 'active':
                    $query->where('products.active', (bool) $value);
                    break;

                case 'show_home':
                    $query->where('products.show_home', (bool) $value);
                    break;

                // Price range filters
                case 'min_price':
                    $query->where('products.price', '>=', $value);
                    break;

                case 'max_price':
                    $query->where('products.price', '<=', $value);
                    break;

                // Stock filters (using HAVING because of aggregation)
                case 'min_stock':
                    $query->havingRaw('SUM(warehouse_product_batches.stock) >= ?', [$value]);
                    break;

                case 'max_stock':
                    $query->havingRaw('SUM(warehouse_product_batches.stock) <= ?', [$value]);
                    break;

                case 'out_of_stock':
                    if ($value) {
                        $query->havingRaw('COALESCE(SUM(warehouse_product_batches.stock), 0) = 0');
                    }
                    break;

                case 'low_stock':
                    if ($value) {
                        $threshold = request('low_stock_threshold') ?? 10;
                        $query->havingRaw('COALESCE(SUM(warehouse_product_batches.stock), 0) > 0')
                              ->havingRaw('COALESCE(SUM(warehouse_product_batches.stock), 0) <= ?', [$threshold]);
                    }
                    break;

                // Reserved stock filter
                case 'min_reserved_stock':
                    $query->where('warehouse_product.reserved_stock', '>=', $value);
                    break;

                case 'max_reserved_stock':
                    $query->where('warehouse_product.reserved_stock', '<=', $value);
                    break;

                case 'has_reserved_stock':
                    if ($value) {
                        $query->where('warehouse_product.reserved_stock', '>', 0);
                    }
                    break;

                

                // Batch count filter
                case 'min_batches':
                    $query->havingRaw('COUNT(DISTINCT warehouse_product_batches.id) >= ?', [$value]);
                    break;

                case 'has_batches':
                    if ($value) {
                        $query->havingRaw('COUNT(DISTINCT warehouse_product_batches.id) > 0');
                    } else {
                        $query->havingRaw('COUNT(DISTINCT warehouse_product_batches.id) = 0');
                    }
                    break;


                // Default: Try to match on products table
                default:
                    if (is_numeric($value)) {
                        $query->where("products.{$key}", '=', $value);
                    } else {
                        $query->where("products.{$key}", 'LIKE', '%' . $value . '%');
                    }
                    break;
            }
        }

        return $query;
    }

    /**
     * Apply filters to batch query
     */
    protected function applyBatchFilters($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if (empty($value) && $value !== '0' && $value !== 0) {
                continue;
            }

            switch ($key) {
                case 'batch_number':
                    $query->where('batch_number', 'LIKE', '%' . $value . '%');
                    break;

                case 'min_stock':
                    $query->where('stock', '>=', $value);
                    break;

                case 'max_stock':
                    $query->where('stock', '<=', $value);
                    break;

                case 'has_expiry':
                    if ($value) {
                        $query->whereNotNull('expiry_date');
                    } else {
                        $query->whereNull('expiry_date');
                    }
                    break;

                case 'expired':
                    if ($value) {
                        $query->where('expiry_date', '<', now());
                    }
                    break;

                case 'not_expired':
                    if ($value) {
                        $query->where(function ($q) {
                            $q->whereNull('expiry_date')
                              ->orWhere('expiry_date', '>=', now());
                        });
                    }
                    break;

                case 'expiring_soon':
                    if ($value) {
                        $days = request('expiring_days') ?? 90;
                        $query->whereBetween('expiry_date', [
                            now(),
                            now()->addDays($days)
                        ]);
                    }
                    break;

                case 'expiry_from':
                    $query->where('expiry_date', '>=', $value);
                    break;

                case 'expiry_to':
                    $query->where('expiry_date', '<=', $value);
                    break;

                case 'created_from':
                    $query->where('created_at', '>=', $value);
                    break;

                case 'created_to':
                    $query->where('created_at', '<=', $value);
                    break;

                case 'status':
                    // Filter by status (expired, expiring_soon, good)
                    $now = now();
                    switch ($value) {
                        case 'expired':
                            $query->where('expiry_date', '<', $now);
                            break;
                        case 'expiring_soon':
                            $days = request('expiring_days') ?? 90;
                            $query->whereBetween('expiry_date', [$now, $now->copy()->addDays($days)]);
                            break;
                        case 'good':
                            $days = request('expiring_days') ?? 90;
                            $query->where(function ($q) use ($now, $days) {
                                $q->whereNull('expiry_date')
                                  ->orWhere('expiry_date', '>', $now->copy()->addDays($days));
                            });
                            break;
                    }
                    break;

                default:
                    if (is_numeric($value)) {
                        $query->where($key, '=', $value);
                    } else {
                        $query->where($key, 'LIKE', '%' . $value . '%');
                    }
                    break;
            }
        }

        return $query;
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
