<?php

namespace App\Repositories;

use App\Interfaces\ProductRateRepositoryInterface;
use App\Models\ProductRating;
use Illuminate\Database\Eloquent\Model;

class ProductRateRepository extends CrudRepository implements ProductRateRepositoryInterface
{
    protected Model $model;

    public function __construct(ProductRating $model)
    {
        $this->model = $model;
    }
}
