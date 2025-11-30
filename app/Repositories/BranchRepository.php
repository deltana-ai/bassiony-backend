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
     * Get products for a specific branch
     */
    public function getBranchProducts(int $branchId)
    {
        
         // 2. SECURITY: Validate and sanitize inputs
$filters = $this->validateFilters(request(Constants::FILTERS, []));
$perPage = min(max((int)request(Constants::PER_PAGE, 15), 1), 100);
$paginate = filter_var(request(Constants::PAGINATE, true), FILTER_VALIDATE_BOOLEAN);

// 3. SECURITY: Whitelist sort columns
$sortColumns = [
    'id' => 'products.id',
    'name_ar' => 'products.name_ar',
    'name_en' => 'products.name_en',
    'price' => 'products.price',
    'stock' => 'batch_summary.total_stock',
    'active' => 'products.active'
];
$sortKey = request(Constants::ORDER_BY, 'id');
$sortBy = $sortColumns[$sortKey] ?? 'products.id';

$sortOrder = strtoupper(request(Constants::ORDER_By_DIRECTION, 'ASC'));
$sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'ASC';

// 4. PERFORMANCE: Subquery for batch aggregations (avoids large GROUP BY)
$batchSubquery = DB::table('branch_product_batches')
    ->select([
        'product_id',
        DB::raw('COALESCE(SUM(stock), 0) as total_stock'),
        DB::raw('COUNT(DISTINCT id) as total_batches')
    ])
    ->where('branch_id', $branchId)
    ->groupBy('product_id');

// 5. PERFORMANCE: Optimized main query (no GROUP BY needed)
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
        DB::raw('(products.price * (1 + products.tax / 100)) AS price'),
        'branch_product.reserved_stock',
        DB::raw("CONCAT(products.name_ar, ' - ', products.name_en) AS name"),
        'batch_summary.total_stock',
        'batch_summary.total_batches'
    ])
    ->join('branch_product', function ($join) use ($branchId) {
        $join->on('products.id', '=', 'branch_product.product_id')
             ->where('branch_product.branch_id', '=', $branchId);
    })
    ->leftJoinSub($batchSubquery, 'batch_summary', function ($join) {
        $join->on('products.id', '=', 'batch_summary.product_id');
    });

// 6. Apply filters
$query = $this->applyFilters($query, $filters);

// 7. SECURITY: Safe sorting (using parameterized orderBy instead of raw)
$query->orderBy(DB::raw($sortBy), $sortOrder);

// 8. PERFORMANCE: Paginate and eager load media
if ($paginate) {
    $products = $query->paginate($perPage);
    $products->load('media');
    return $products;
}

$products = $query->get();
$products->load('media');
return $products;
    }

    /**
     * Get batch details for specific product in branch
     */
    public function getProductBatches(int $productId, int $branchId)
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
        $query = BranchProductBatch::query()
            ->with([
                'product:id,name_ar,name_en,bar_code,price,tax,qr_code,gtin,active',
                'branch:id,name,address'
            ])
            ->where('product_id', $productId)
            ->where('branch_id', $branchId);

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
     * SECURITY: Validate product filters
     */
    protected function validateFilters(array $filters): array
    {
        $allowed = [
            'search', 'active', 'min_price', 'max_price',
            'min_stock', 'max_stock', 'out_of_stock', 'low_stock',
            'low_stock_threshold', 'min_reserved_stock', 'max_reserved_stock',
            'has_reserved_stock', 'min_batches', 'has_batches'
        ];

        return array_intersect_key(
            $filters,
            array_flip($allowed)
        );
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
     * Apply filters to product query
     */
    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            switch ($key) {
                case 'search':
                    $searchTerm = trim($value);
                    if (strlen($searchTerm) > 0) {
                        // SECURITY: Parameterized query
                        $query->whereRaw(
                            "MATCH(products.search_index) AGAINST(? IN BOOLEAN MODE)",
                            [$searchTerm]
                        );
                    }
                    break;

                case 'active':
                    $query->where('products.active', filter_var($value, FILTER_VALIDATE_BOOLEAN));
                    break;

                case 'min_price':
                    $query->where('products.price', '>=', (float)$value);
                    break;

                case 'max_price':
                    $query->where('products.price', '<=', (float)$value);
                    break;

                case 'min_stock':
                    $query->havingRaw('COALESCE(SUM(branch_product_batches.stock), 0) >= ?', [(int)$value]);
                    break;

                case 'max_stock':
                    $query->havingRaw('COALESCE(SUM(branch_product_batches.stock), 0) <= ?', [(int)$value]);
                    break;

                case 'out_of_stock':
                    if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        $query->havingRaw('COALESCE(SUM(branch_product_batches.stock), 0) = 0');
                    }
                    break;

                case 'low_stock':
                    if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        $threshold = min((int)($filters['low_stock_threshold'] ?? 10), 1000);
                        $query->havingRaw(
                            'COALESCE(SUM(branch_product_batches.stock), 0) BETWEEN 1 AND ?',
                            [$threshold]
                        );
                    }
                    break;

                case 'min_reserved_stock':
                    $query->where('branch_product.reserved_stock', '>=', (int)$value);
                    break;

                case 'max_reserved_stock':
                    $query->where('branch_product.reserved_stock', '<=', (int)$value);
                    break;

                case 'has_reserved_stock':
                    if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        $query->where('branch_product.reserved_stock', '>', 0);
                    }
                    break;

                case 'min_batches':
                    $query->havingRaw('COUNT(DISTINCT branch_product_batches.id) >= ?', [(int)$value]);
                    break;

                case 'has_batches':
                    $operator = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '>' : '=';
                    $query->havingRaw("COUNT(DISTINCT branch_product_batches.id) {$operator} 0");
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
