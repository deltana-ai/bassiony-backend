<?php

namespace App\Repositories;

use App\Helpers\Constants;
use App\Interfaces\PharmacistRepositoryInterface;
use App\Interfaces\PharmacyRepositoryInterface;
use App\Models\Pharmacist;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Notifications\SendPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PharmacyRepository extends CrudRepository implements PharmacyRepositoryInterface
{
    
    protected Model $model;
    protected $employee_repo ;
    public $dashboard_type = "pharmacy";
    public $table = "pharmacies";
    public $employee_table = "pharmacists";
    public function __construct(Pharmacy $model ,PharmacistRepositoryInterface $employee_repo)
    {
        $this->model = $model;
        $this->employee_repo = $employee_repo;

    }


    public function createPharmacywithUser( array $data )
    {
        return DB::transaction(function () use ($data) {

            $employee_data = $this->handleData($data);

            unset($data["email"]);

            $pharmacy = $this->create($data);

            $employee_data["pharmacy_id"] = $pharmacy->id;

            $password = $employee_data["un_hash"];

            unset($employee_data["un_hash"]);

            $employee = $this->employee_repo->create($employee_data);

            $employee ->assignRole("pharmacy_owner");

            $employee->notify(new SendPassword($password ,$this->dashboard_type));

            return ["employee"=>$employee,"password"=>$password] ;
        });

    }

    public function updatePharmacywithUser( array $data  ,$pharmacy_id )
    {
        return DB::transaction(function () use ($data,$pharmacy_id) {

            $employee_data = $this->handleData($data);

            unset($data["email"]);

            $this->update($data , $pharmacy_id) ;

            $employee_data["pharmacy_id"] = $pharmacy_id;

            $password = $employee_data["un_hash"];

            unset($employee_data["un_hash"]);

            $employee = Pharmacist::where('is_owner',1)->first();

            $employee ->update($employee_data);

            $employee->notify(new SendPassword($password ,$this->dashboard_type));

            return ["employee"=>$employee,"password"=>$password] ;
        });

    }



    public function deletePharmacywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            $this->employee_repo->model::whereIn('pharmacy_id', $ids)->delete();
            $this->model::whereIn('id', $ids)->delete();

        });

    }


    public function restorePharmacywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            $this->model::withTrashed()
            ->whereIn('id', $ids)
            ->restore();

            $this->employee_repo->model::withTrashed()
                ->whereIn('pharmacy_id', $ids)
                ->restore();
        });

    }


 
    protected function handleData(array $data)
    {

        $code = rand(1000,9999);

        $password = rand(1000,9999)."Admin".rand(1000,9999);

        $employee["name"] = $data["name"]."_admin".$code ;

        $employee["active"] = 1;

        $employee["email"] = $data["email"];

        $employee["password"] = Hash::make($password) ;
        $employee["un_hash"] = $password; 
     
        $employee["is_owner"] = 1;
        return $employee;
      
    }



     public function getPharmacyProducts(int $pharmacyId)
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
                'products.rating',
                'products.bar_code',
                'products.category_id',
                'products.brand_id',
                'categories.name as category_name',
                'brands.name as brand_name',
                DB::raw('COALESCE(SUM(branch_product.reserved_stock), 0) as total_reserved_stock'),
                DB::raw('COALESCE(SUM(branch_product_batches.stock), 0) as total_stock'),
                DB::raw('COUNT(DISTINCT branch_product_batches.id) as total_batches'),
                DB::raw('COUNT(DISTINCT branches.id) as total_branches')
            ])
            ->with(['media']) // Load product images
            ->join('branch_product', 'products.id', '=', 'branch_product.product_id')
            ->join('branches', function($join) use ($pharmacyId) {
                $join->on('branch_product.branch_id', '=', 'branches.id')
                     ->where('branches.pharmacy_id', '=', $pharmacyId);
            })
            ->leftJoin('branch_product_batches', function($join) {
                $join->on('products.id', '=', 'branch_product_batches.product_id')
                     ->on('branches.id', '=', 'branch_product_batches.branch_id');
            })
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->groupBy([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.active',
                'products.show_home',
                'products.rating',
                'products.bar_code',
                'products.category_id',
                'products.brand_id',
                'categories.name',
                'brands.name'
            ]);

        // Apply all filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        if ($paginate) {
            $products = $query->paginate($perPage);
            
            
            
            return $products;
        } else {
            return $query->get();
        }
    
        
    }



    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if (empty($value) && $value !== '0' && $value !== 0 && $value !== false) {
                continue;
            }

            switch ($key) {
                // ========== PRODUCT FILTERS ==========
                
                // Search (name, description, barcode)
                case 'search':
                    $query->where(function ($q) use ($value) {
                        $q->where('products.name', 'LIKE', '%' . $value . '%')
                          ->orWhere('products.description', 'LIKE', '%' . $value . '%')
                          ->orWhere('products.bar_code', 'LIKE', '%' . $value . '%');
                    });
                    break;

                // Product name only
                case 'product_name':
                    $query->where('products.name', 'LIKE', '%' . $value . '%');
                    break;

                // Barcode exact or partial match
                case 'bar_code':
                    $query->where('products.bar_code', 'LIKE', '%' . $value . '%');
                    break;

                case 'bar_code_exact':
                    $query->where('products.bar_code', $value);
                    break;

                // Product status
                case 'active':
                    $query->where('products.active', (bool) $value);
                    break;

                case 'show_home':
                    $query->where('products.show_home', (bool) $value);
                    break;

                // Price filters
                case 'min_price':
                    $query->where('products.price', '>=', $value);
                    break;

                case 'max_price':
                    $query->where('products.price', '<=', $value);
                    break;

                case 'price_range':
                    if (is_array($value) && count($value) == 2) {
                        $query->whereBetween('products.price', [$value[0], $value[1]]);
                    }
                    break;

                // Rating filters
                case 'min_rating':
                    $query->where('products.rating', '>=', $value);
                    break;

                case 'max_rating':
                    $query->where('products.rating', '<=', $value);
                    break;

                // ========== CATEGORY FILTERS ==========
                
                case 'category_id':
                    if (is_array($value)) {
                        $query->whereIn('products.category_id', $value);
                    } else {
                        $query->where('products.category_id', $value);
                    }
                    break;

                case 'category_name':
                    $query->where('categories.name', 'LIKE', '%' . $value . '%');
                    break;

                case 'has_category':
                    if ($value) {
                        $query->whereNotNull('products.category_id');
                    } else {
                        $query->whereNull('products.category_id');
                    }
                    break;

                // ========== BRAND FILTERS ==========
                
                case 'brand_id':
                    if (is_array($value)) {
                        $query->whereIn('products.brand_id', $value);
                    } else {
                        $query->where('products.brand_id', $value);
                    }
                    break;

                case 'brand_name':
                    $query->where('brands.name', 'LIKE', '%' . $value . '%');
                    break;

                case 'has_brand':
                    if ($value) {
                        $query->whereNotNull('products.brand_id');
                    } else {
                        $query->whereNull('products.brand_id');
                    }
                    break;

                // ========== WAREHOUSE FILTERS ==========
                
               

                case 'branch_active':
                    $query->where('branches.active', (bool) $value);
                    break;

                case 'min_branches':
                    $query->havingRaw('COUNT(DISTINCT branches.id) >= ?', [$value]);
                    break;

                case 'max_branches':
                    $query->havingRaw('COUNT(DISTINCT branches.id) <= ?', [$value]);
                    break;

                // ========== STOCK FILTERS ==========
                
                // Total stock filters
                case 'min_stock':
                    $query->havingRaw('COALESCE(SUM(branch_product_batches.stock), 0) >= ?', [$value]);
                    break;

                case 'max_stock':
                    $query->havingRaw('COALESCE(SUM(branch_product_batches.stock), 0) <= ?', [$value]);
                    break;

                case 'stock_range':
                    if (is_array($value) && count($value) == 2) {
                        $query->havingRaw(
                            'COALESCE(SUM(branch_product_batches.stock), 0) BETWEEN ? AND ?',
                            [$value[0], $value[1]]
                        );
                    }
                    break;

                case 'out_of_stock':
                    if ($value) {
                        $query->havingRaw('COALESCE(SUM(branch_product_batches.stock), 0) = 0');
                    }
                    break;

                case 'in_stock':
                    if ($value) {
                        $query->havingRaw('COALESCE(SUM(branch_product_batches.stock), 0) > 0');
                    }
                    break;

                case 'low_stock':
                    if ($value) {
                        $threshold = $filters['low_stock_threshold'] ?? 10;
                        $query->havingRaw(
                            'COALESCE(SUM(branch_product_batches.stock), 0) > 0 AND COALESCE(SUM(branch_product_batches.stock), 0) <= ?',
                            [$threshold]
                        );
                    }
                    break;

                // Reserved stock filters
                case 'min_reserved_stock':
                    $query->havingRaw('COALESCE(SUM(branch_product.reserved_stock), 0) >= ?', [$value]);
                    break;

                case 'max_reserved_stock':
                    $query->havingRaw('COALESCE(SUM(branch_product.reserved_stock), 0) <= ?', [$value]);
                    break;

                case 'has_reserved_stock':
                    if ($value) {
                        $query->havingRaw('COALESCE(SUM(branch_product.reserved_stock), 0) > 0');
                    }
                    break;

                // Available stock (total - reserved)
                case 'min_available_stock':
                    $query->havingRaw(
                        '(COALESCE(SUM(branch_product_batches.stock), 0) - COALESCE(SUM(branch_product.reserved_stock), 0)) >= ?',
                        [$value]
                    );
                    break;

                case 'max_available_stock':
                    $query->havingRaw(
                        '(COALESCE(SUM(branch_product_batches.stock), 0) - COALESCE(SUM(branch_product.reserved_stock), 0)) <= ?',
                        [$value]
                    );
                    break;

               

                

                  

                // ========== DATE FILTERS ==========
                
                case 'created_from':
                    $query->where('products.created_at', '>=', $value);
                    break;

                case 'created_to':
                    $query->where('products.created_at', '<=', $value);
                    break;

                case 'updated_from':
                    $query->where('products.updated_at', '>=', $value);
                    break;

                case 'updated_to':
                    $query->where('products.updated_at', '<=', $value);
                    break;

                // ========== STOCK STATUS ==========
                
                case 'stock_status':
                    switch ($value) {
                        case 'out_of_stock':
                            $query->havingRaw('COALESCE(SUM(branch_product_batches.stock), 0) = 0');
                            break;
                        case 'low_stock':
                            $threshold = $filters['low_stock_threshold'] ?? 10;
                            $query->havingRaw(
                                'COALESCE(SUM(branch_product_batches.stock), 0) > 0 AND COALESCE(SUM(branch_product_batches.stock), 0) <= ?',
                                [$threshold]
                            );
                            break;
                        case 'medium_stock':
                            $min = $filters['medium_stock_min'] ?? 11;
                            $max = $filters['medium_stock_max'] ?? 50;
                            $query->havingRaw(
                                'COALESCE(SUM(branch_product_batches.stock), 0) BETWEEN ? AND ?',
                                [$min, $max]
                            );
                            break;
                        case 'in_stock':
                            $threshold = $filters['in_stock_threshold'] ?? 50;
                            $query->havingRaw(
                                'COALESCE(SUM(branch_product_batches.stock), 0) > ?',
                                [$threshold]
                            );
                            break;
                    }
                    break;

                // Skip pagination and sorting parameters
                case 'per_page':
                case 'paginate':
                case 'sort_by':
                case 'sort_order':
                case 'low_stock_threshold':
                case 'expiring_days':
                case 'medium_stock_min':
                case 'medium_stock_max':
                case 'in_stock_threshold':
                    // Skip these as they're handled separately
                    break;

                // Default: Try to match on products table
                default:
                    if (strpos($key, 'products.') === 0) {
                        if (is_numeric($value)) {
                            $query->where($key, '=', $value);
                        } else {
                            $query->where($key, 'LIKE', '%' . $value . '%');
                        }
                    }
                    break;
            }
        }

        return $query;
    }

}
