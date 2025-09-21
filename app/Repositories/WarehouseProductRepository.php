<?php

namespace App\Repositories;

use App\Interfaces\WarehouseProductRepositoryInterface;
use App\Models\Product;
use App\Models\WarehouseProduct;
use Illuminate\Database\Eloquent\Model;

class WarehouseProductRepository extends CrudRepository implements WarehouseProductRepositoryInterface
{
    protected Model $model;

    public function __construct(WarehouseProduct $model)
    {
        $this->model = $model;
    }
}