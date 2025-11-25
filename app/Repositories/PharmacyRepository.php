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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
            unset($data["password"]);

            $pharmacy = $this->create($data);

            $employee_data["pharmacy_id"] = $pharmacy->id;
            $pharmacy_permission= Permission::where('guard_name','pharmacists')->where('name','manage-pharmacy')->first();

            $employee = $this->employee_repo->create($employee_data);
            $roleName = 'pharmacy_owner_' . $pharmacy->id;
            $superpharmacist = Role::firstOrCreate(['name' => $roleName,'guard_name'=>'pharmacists','pharmacy_id'=>$pharmacy->id]);
            $superpharmacist->givePermissionTo($pharmacy_permission);

            $employee ->assignRole($superpharmacist);

            return $employee ;
        });

    }

    public function updatePharmacywithUser( array $data  ,$pharmacy_id )
    {
        return DB::transaction(function () use ($data,$pharmacy_id) {

            $employee_data = $this->handleData($data);

            unset($data["email"]);
            unset($data["password"]);
            $this->update($data , $pharmacy_id) ;

            $employee_data["pharmacy_id"] = $pharmacy_id;

            $employee = Pharmacist::where('pharmacy_id', $pharmacy_id)->where('is_owner',1)->first();

            $employee ->update($employee_data);


            return $employee;
        });

    }



    public function deletePharmacywithUsers(array $ids )
    {
        
        return DB::transaction(function () use ($ids) {
            Pharmacist::whereIn('pharmacy_id', $ids)->delete();
            $this->model::whereIn('id', $ids)->delete();

        });

    }


    public function restorePharmacywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            $this->model::withTrashed()
            ->whereIn('id', $ids)
            ->restore();

            Pharmacist::withTrashed()
                ->whereIn('pharmacy_id', $ids)
                ->restore();
        });

    }


 
    protected function handleData(array $data)
    {

        $password = $data["password"];

        $code = mt_rand(1000, 9999);

        $employee["name"] = $data["name"]."_admin".$code ;

        $employee["active"] = 1;

        $employee["email"] = $data["email"];
        if (isset($data["password"])) {
             $employee["password"] = Hash::make($data["password"]) ;

        }
     
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
                'products.name_ar',
                'products.name_en',
                'products.description',
                'products.price AS price_without_tax',
                'products.tax',
                DB::raw('(products.price + (products.price * products.tax / 100)) AS price'),
                'products.active',
                'products.rating',
                'products.bar_code',
                'products.qr_code',
                'products.gtin',
                'products.scientific_name',
                'products.active_ingredients',
                'products.dosage_form',
                'products.category_id',
                'products.brand_id',
                'categories.name as category_name',
                'brands.name as brand_name',
                DB::raw("CONCAT(products.name_ar, ' - ', products.name_en) AS name"),
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
                'products.name_ar',
                'products.name_en',
                'products.description',
                'products.price',
                'products.tax',
                'products.active',
                'products.rating',
                'products.bar_code',
                'products.qr_code',
                'products.gtin',
                'products.scientific_name',
                'products.active_ingredients',
                'products.dosage_form',
                'products.category_id',
                'products.brand_id',
                'categories.name',
                'brands.name'
            ]);

        // Apply all filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        return $paginate ? $query->paginate($perPage) : $query->get();

    
        
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
                    $query->whereRaw("MATCH(products.search_index) AGAINST(? IN BOOLEAN MODE)", [$value]);

                    break;

                // Product name only
                

                // Barcode exact or partial match
                case 'bar_code':
                    $query->where('products.bar_code', 'LIKE', '%' . $value . '%');
                    break;

                case 'bar_code_exact':
                    $query->where('products.bar_code', $value);
                    break;
                case 'qr_code':
                    $query->where('products.qr_code', $value);
                    break;

                case 'gtin':
                    $query->where('products.gtin', $value);
                    break;
                // Product status
                case 'active':
                    $query->where('products.active', (bool) $value);
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
