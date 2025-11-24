<?php
namespace App\Repositories;

use App\Helpers\Constants;
use App\Interfaces\CompanyOfferRepositoryInterface;
use App\Models\CompanyOffer;
class CompanyOfferRepository extends CrudRepository implements CompanyOfferRepositoryInterface
{
    public function __construct(CompanyOffer $model)
    {
        $this->model = $model;
    }

    public  function allOffers($with = [], $conditions = [], $columns = array('*') )
    {

         $order_by = request(Constants::ORDER_BY) ?? "id";
        $deleted = request(Constants::Deleted) ?? false;
        
        $order_by_direction = request(Constants::ORDER_By_DIRECTION) ?? "asc";
        $filter_operator = request(Constants::FILTER_OPERATOR) ?? "=";
        $filters = request(Constants::FILTERS) ?? [];
        $per_page = request(Constants::PER_PAGE) ?? 15;
        $paginate = request(Constants::PAGINATE) ?? true;
        $query = $this->model;
        if ($deleted == true) {
            $query = $query->onlyTrashed();
        }

        $all_conditions = array_merge($conditions, $filters);
        foreach ($all_conditions as $key => $value) {
            if (is_numeric($value)) {
                $query = $query->where($key, '=', $value);
            } else {
                $query = $query->where($key, 'LIKE', '%' . $value . '%');
            }
        }
       
         if ($searchIndex = request('search_index')) {
           $query =  $query->whereHas('warehouseProduct.product', function($q) use ($searchIndex) {
                $q->whereRaw(
                    "MATCH(search_index) AGAINST(? IN BOOLEAN MODE)",
                    [$searchIndex]
                );
            });
        }
        if ($productId = request('product_id')) {
                
            $query =   $query->whereHas('warehouseProduct.product', function($q2) use ($productId) {
                $q2->where('product_id', $productId);
            });

        }

        if (!is_null(request('is_active'))) {
            $isActive = request('is_active');            
            if ($isActive == 1) {
                $isActive = request('is_active');
              $query =   $query->where('active', 1)
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
                
            } elseif ($isActive == 0) {
               $query =  $query->where('active', false)
                     ->orWhereDate('end_date', '<', now());
               
            }
        }
        if ($companyName = request('company_name')) {
           $query =  $query->whereHas('company', function($q) use ($companyName) {
                $q->where('name', 'LIKE', "%{$companyName}%");
            });
        }

        if ($minDiscount = request('discount_min')) {
           $query =  $query->where('discount', '>=', $minDiscount);
        }

        if ($maxDiscount = request('discount_max')) {
           $query =  $query->where('discount', '<=', $maxDiscount);
        }

       
        if (isset($order_by) && !empty($with))
            $query = $query->with($with)->orderBy($order_by, $order_by_direction);
        if ($paginate && !empty($with))
            return $query->with($with)->paginate($per_page, $columns);
        if (isset($order_by))
            $query = $query->orderBy($order_by, $order_by_direction);
        if ($paginate)
            return $query->paginate($per_page, $columns);
        
    
    
        if (!empty($with))
            return $query->with($with)->get($columns);
        else
            return $query->get($columns);
       
    }
}