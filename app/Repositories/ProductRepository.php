<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Constants;
class ProductRepository extends CrudRepository implements ProductRepositoryInterface
{
    protected Model $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }



    public function anotherAll($with = [], $conditions = [], $columns = ['*'], $customQuery = null)
    {
        $order_by = request(Constants::ORDER_BY) ?? "id";
        $deleted = request(Constants::Deleted) ?? false;
        $order_by_direction = request(Constants::ORDER_By_DIRECTION) ?? "asc";
        $filters = request(Constants::FILTERS) ?? [];
        $per_page = request(Constants::PER_PAGE) ?? 15;
        $paginate = request(Constants::PAGINATE) ?? true;

        $query = $this->model->newQuery();

        if ($deleted) {
            $query = $query->onlyTrashed();
        }

        $all_conditions = array_merge($conditions, $filters);
        foreach ($all_conditions as $key => $value) {
            if (is_numeric($value)) {
                $query->where($key, '=', $value);
            } else {
                $query->where($key, 'LIKE', '%' . $value . '%');
            }
        }

        // تطبيق الـ customQuery مرة واحدة
        if (is_callable($customQuery)) {
            $query = $customQuery($query);
        }

        // تطبيق الـ with إذا موجود
        if (!empty($with)) {
            $query = $query->with($with);
        }

        // تطبيق orderBy
        if (isset($order_by)) {
            $query = $query->orderBy($order_by, $order_by_direction);
        }

        // paginate أو get
        return $paginate ? $query->paginate($per_page, $columns) : $query->get($columns);
    }


    
}
