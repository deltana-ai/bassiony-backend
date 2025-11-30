<?php

namespace App\Repositories;

use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constants;
use App\Models\Product;
use App\Models\ResponseOffer;
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
         $warehouse = Warehouse::select('id', 'company_id') 
        ->findOrFail($warehouseId); 
 
    $filters = $this->validateFilters(request(Constants::FILTERS, [])); 
    $perPage = min(max((int)request(Constants::PER_PAGE, 15), 1), 100); 
    $paginate = filter_var(request(Constants::PAGINATE, true), FILTER_VALIDATE_BOOLEAN); 
     
    $sortColumns = [ 
        'id' => 'products.id', 
        'name_ar' => 'products.name_ar', 
        'name_en' => 'products.name_en', 
        'stock' => 'batch_summary.total_stock', 
        'price' => 'price_after_discount_without_tax' 
    ]; 
    $sortKey = request(Constants::ORDER_BY, 'id'); 
    $sortBy = $sortColumns[$sortKey] ?? 'products.id'; 
     
    $sortOrder = strtoupper(request(Constants::ORDER_By_DIRECTION, 'ASC')); 
    $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'ASC'; 

    // Subquery for batch aggregations (only groups by product_id)
    $batchSubquery = DB::table('warehouse_product_batches')
        ->select([
            'product_id',
            DB::raw('COALESCE(SUM(stock), 0) as total_stock'),
            DB::raw('COUNT(DISTINCT id) as total_batches')
        ])
        ->where('warehouse_id', $warehouseId)
        ->groupBy('product_id');

    // Main query without complex GROUP BY
    $query = Product::query() 
        ->select([ 
            'products.id', 
            'products.name_ar', 
            'products.name_en', 
            'products.active', 
            'products.bar_code', 
            'products.qr_code', 
            'products.gtin', 
            'products.scientific_name', 
            'products.active_ingredients', 
            'products.description', 
            'products.dosage_form', 
            'products.price AS price_without_tax', 
            'products.tax', 
            'warehouse_product.reserved_stock', 
            'company_prices.discount_percent As company_discount_percent', 
            DB::raw("CONCAT(products.name_ar, ' - ', products.name_en) AS name"), 
            'batch_summary.total_stock',
            'batch_summary.total_batches',
             
            // Price calculations 
            DB::raw('products.price * (1 - COALESCE(company_prices.discount_percent, 0) / 100) 
                AS price_after_discount_without_tax'), 
             
            DB::raw('products.price * (1 - COALESCE(company_prices.discount_percent, 0) / 100) * (1 + products.tax / 100) 
                AS price_after_discount_with_tax'), 
        ]) 
        ->join('warehouse_product', function ($join) use ($warehouseId) { 
            $join->on('products.id', '=', 'warehouse_product.product_id') 
                ->where('warehouse_product.warehouse_id', '=', $warehouseId); 
        }) 
        ->leftJoinSub($batchSubquery, 'batch_summary', function ($join) {
            $join->on('products.id', '=', 'batch_summary.product_id');
        })
        ->leftJoin('company_prices', function ($join) use ($warehouse) { 
            $join->on('products.id', '=', 'company_prices.product_id') 
                ->where('company_prices.company_id', '=', $warehouse->company_id); 
        });

    // Apply filters 
    $query = $this->applyFilters($query, $filters); 
     
    // Apply sorting 
    $query->orderByRaw("{$sortBy} {$sortOrder}"); 

    // Return results 
    return $paginate ? $query->paginate($perPage) : $query->get(); 
}



     /**
     * Get batch details for specific product in warehouse
     */
     public function getProductBatches(int $productId, int $warehouseId)
    {

        // 2. SECURITY: Validate inputs
        $filters = $this->validateBatchFilters(request(Constants::FILTERS, []));
        $perPage = min(max((int)request(Constants::PER_PAGE, 50), 1), 100);
        $paginate = filter_var(request(Constants::PAGINATE, true), FILTER_VALIDATE_BOOLEAN);
        
        // 3. SECURITY: Whitelist sort columns
        $sortColumns = [
            'id' => 'id',
            'batch_number' => 'batch_number',
            'stock' => 'stock',
            'expiry_date' => 'expiry_date',
            'created_at' => 'created_at'
        ];
        $sortKey = request(Constants::ORDER_BY, 'created_at');
        $sortBy = $sortColumns[$sortKey] ?? 'created_at';
        
        $sortOrder = strtoupper(request(Constants::ORDER_By_DIRECTION, 'DESC'));
        $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'DESC';

        // 4. Build query
        $query = WarehouseProductBatch::query()
            ->with([
                'product:id,name_ar,name_en,bar_code,price,tax,qr_code,gtin,active',
                'warehouse:id,name,address'
            ])
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        // 5. Apply filters
        $query = $this->applyBatchFilters($query, $filters);

        // 6. Apply sorting
        $query->orderByRaw("{$sortBy} {$sortOrder}");
        
        if ($sortBy !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }

        // 7. PERFORMANCE: Always paginate
        return $paginate ? $query->paginate($perPage) : $query->limit(1000)->get();
    }

    
    

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            switch ($key) {
                case 'search':
                    // Sanitize search input
                    $searchTerm = trim($value);
                    if (strlen($searchTerm) > 0) {
                        $query->whereRaw(
                            "MATCH(products.search_index) AGAINST(? IN BOOLEAN MODE)", 
                            [$searchTerm]
                        );
                    }
                    break;

                case 'active':
                    $query->where('products.active', (bool)$value);
                    break;

                case 'min_price':
                    $query->havingRaw('price_after_discount_without_tax >= ?', [(float)$value]);
                    break;

                case 'max_price':
                    $query->havingRaw('price_after_discount_without_tax <= ?', [(float)$value]);
                    break;

                case 'min_price_with_tax':
                    $query->havingRaw('price_after_discount_with_tax >= ?', [(float)$value]);
                    break;

                case 'max_price_with_tax':
                    $query->havingRaw('price_after_discount_with_tax <= ?', [(float)$value]);
                    break;

                case 'min_stock':
                    $query->havingRaw('COALESCE(SUM(warehouse_product_batches.stock), 0) >= ?', [(int)$value]);
                    break;

                case 'max_stock':
                    $query->havingRaw('COALESCE(SUM(warehouse_product_batches.stock), 0) <= ?', [(int)$value]);
                    break;

                case 'out_of_stock':
                    if ((bool)$value) {
                        $query->havingRaw('COALESCE(SUM(warehouse_product_batches.stock), 0) = 0');
                    }
                    break;

                case 'low_stock':
                    if ((bool)$value) {
                        $threshold = min((int)($filters['low_stock_threshold'] ?? 10), 1000);
                        $query->havingRaw('COALESCE(SUM(warehouse_product_batches.stock), 0) BETWEEN 1 AND ?', [$threshold]);
                    }
                    break;

                case 'min_reserved_stock':
                    $query->where('warehouse_product.reserved_stock', '>=', (int)$value);
                    break;

                case 'max_reserved_stock':
                    $query->where('warehouse_product.reserved_stock', '<=', (int)$value);
                    break;

                case 'has_reserved_stock':
                    if ((bool)$value) {
                        $query->where('warehouse_product.reserved_stock', '>', 0);
                    }
                    break;

                case 'min_batches':
                    $query->havingRaw('COUNT(DISTINCT warehouse_product_batches.id) >= ?', [(int)$value]);
                    break;

                case 'has_batches':
                    $operator = (bool)$value ? '>' : '=';
                    $query->havingRaw("COUNT(DISTINCT warehouse_product_batches.id) {$operator} 0");
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
            if (is_null($value) || $value === '') {
                continue;
            }

            switch ($key) {
                case 'batch_number':
                    // SECURITY: Parameterized LIKE
                    $query->where('batch_number', 'LIKE', '%' . addslashes($value) . '%');
                    break;

                case 'min_stock':
                    $query->where('stock', '>=', (int)$value);
                    break;

                case 'max_stock':
                    $query->where('stock', '<=', (int)$value);
                    break;

                case 'has_expiry':
                    if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        $query->whereNotNull('expiry_date');
                    } else {
                        $query->whereNull('expiry_date');
                    }
                    break;

                case 'expired':
                    if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        $query->where('expiry_date', '<', now());
                    }
                    break;

                case 'not_expired':
                    if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        $query->where(function ($q) {
                            $q->whereNull('expiry_date')
                              ->orWhere('expiry_date', '>=', now());
                        });
                    }
                    break;

                case 'expiring_soon':
                    if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        $days = min((int)($filters['expiring_days'] ?? 90), 365);
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
                    $now = now();
                    switch ($value) {
                        case 'expired':
                            $query->where('expiry_date', '<', $now);
                            break;
                        case 'expiring_soon':
                            $days = min((int)($filters['expiring_days'] ?? 90), 365);
                            $query->whereBetween('expiry_date', [
                                $now,
                                $now->copy()->addDays($days)
                            ]);
                            break;
                        case 'good':
                            $days = min((int)($filters['expiring_days'] ?? 90), 365);
                            $query->where(function ($q) use ($now, $days) {
                                $q->whereNull('expiry_date')
                                  ->orWhere('expiry_date', '>', $now->copy()->addDays($days));
                            });
                            break;
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


    protected function validateFilters(array $filters): array
    {
        $allowedFilters = [
            'search' => 'string|max:255',
            'active' => 'boolean',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0',
            'min_price_with_tax' => 'numeric|min:0',
            'max_price_with_tax' => 'numeric|min:0',
            'min_stock' => 'integer|min:0',
            'max_stock' => 'integer|min:0',
            'out_of_stock' => 'boolean',
            'low_stock' => 'boolean',
            'low_stock_threshold' => 'integer|min:1|max:1000',
            'min_reserved_stock' => 'integer|min:0',
            'max_reserved_stock' => 'integer|min:0',
            'has_reserved_stock' => 'boolean',
            'min_batches' => 'integer|min:0',
            'has_batches' => 'boolean'
        ];

        $validated = [];
        foreach ($filters as $key => $value) {
            if (isset($allowedFilters[$key])) {
                // Basic validation
                $validated[$key] = $value;
            }
        }

        return $validated;
    }


     /**
     * SECURITY: Validate batch filters
     */
    protected function validateBatchFilters(array $filters): array
    {
        $allowed = [
            'batch_number', 'min_stock', 'max_stock', 'has_expiry',
            'expired', 'not_expired', 'expiring_soon', 'expiring_days',
            'expiry_from', 'expiry_to', 'created_from', 'created_to', 'status'
        ];

        return array_intersect_key(
            $filters,
            array_flip($allowed)
        );
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
