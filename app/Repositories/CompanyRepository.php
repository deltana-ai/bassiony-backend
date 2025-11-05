<?php

namespace App\Repositories;

use App\Interfaces\CompanyRepositoryInterface;
use App\Models\Company;
use Hash;
use Illuminate\Database\Eloquent\Model;
use App\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use App\Models\Product;
use App\Notifications\SendPassword;
use Illuminate\Support\Facades\DB;

class CompanyRepository extends CrudRepository implements CompanyRepositoryInterface
{
    protected Model $model;
    protected $employee_repo ;
    public $dashboard_type = "company";
    public $table = "companies";
    public $employee_table = "employees";
    public function __construct(Company $model ,EmployeeRepositoryInterface $employee_repo)
    {
        $this->model = $model;
        $this->employee_repo = $employee_repo;

    }


    public function createCompanywithUser( array $data )
    {
        return DB::transaction(function () use ($data) {

            $employee_data = $this->handleData($data);

            unset($data["email"]);
            
            $company = $this->create($data);

            $employee_data["company_id"] = $company->id;

            $password = $employee_data["un_hash"];

            unset($employee_data["un_hash"]);

            $employee = $this->employee_repo->create($employee_data);
            
            $employee ->assignRole("company_owner");
            
            $employee->notify(new SendPassword($password ,$this->dashboard_type));

            return ["employee"=>$employee,"password"=>$password] ;
        });

    }

    public function updateCompanywithUser( array $data  ,$company_id )
    {
        return DB::transaction(function () use ($data,$company_id) {

            $employee_data = $this->handleData($data);

            unset($data["email"]);

            $company = $this->find($company_id);

            $company->update($data) ;
             
            $employee_data["company_id"] = $company->id;

            $password = $employee_data["un_hash"];

            unset($employee_data["un_hash"]);

            $employee = Employee::where('is_owner',1)->first();
      
            $employee ->update($employee_data);
            
            $employee->notify(new SendPassword($password ,$this->dashboard_type));

            return ["employee"=>$employee,"password"=>$password] ;
        });

    }



    public function deleteCompanywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            Employee::whereIn('company_id', $ids)->delete();
            $this->model::whereIn('id', $ids)->delete();

        });

    }


    public function restoreCompanywithUsers(array $ids )
    {
        return DB::transaction(function () use ($ids) {
            $this->model::withTrashed()
            ->whereIn('id', $ids)
            ->restore();

            $this->employee_repo->model::withTrashed()
                ->whereIn('company_id', $ids)
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
/////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
    public function getCompanyProducts(int $companyId, array $filters = [])
    {

        $query = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.active',
                'categories.name as category_name',
                'brands.name as brand_name',
                DB::raw('COALESCE(SUM(warehouse_product.reserved_stock), 0) as total_reserved_stock'),
                DB::raw('COALESCE(SUM(warehouse_product_batches.stock), 0) as total_stock'),
                DB::raw('COUNT(DISTINCT warehouse_product_batches.id) as total_batches'),
                DB::raw('COUNT(DISTINCT warehouses.id) as total_warehouses')
            ])
            ->join('warehouse_product', 'products.id', '=', 'warehouse_product.product_id')
            ->join('warehouses', function($join) use ($companyId) {
                $join->on('warehouse_product.warehouse_id', '=', 'warehouses.id')
                     ->where('warehouses.company_id', '=', $companyId);
            })
            ->leftJoin('warehouse_product_batches', function($join) {
                $join->on('products.id', '=', 'warehouse_product_batches.product_id')
                     ->on('warehouses.id', '=', 'warehouse_product_batches.warehouse_id');
            })
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->groupBy([
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.active',
                'categories.name',
                'brands.name'
            ]);

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('products.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('products.description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('products.bar_code',  $filters['search']);
            });
        }

        

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouses.id', $filters['warehouse_id']);
        }
        if (!empty($filters['warehouse_name'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('warehouses.name', 'like', '%' . $filters['warehouse_name'] . '%');
                
            });
        }

        $perPage = $filters['per_page'] ?? 15;
        $products = $query->paginate($perPage);

        return $products;

        
    }

    

    
    




}
