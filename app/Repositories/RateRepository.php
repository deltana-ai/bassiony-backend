<?php

namespace App\Repositories;

use App\Interfaces\RateRepositoryInterface;
use App\Models\ProductRating;

use Illuminate\Database\Eloquent\Model;

class RateRepository extends CrudRepository implements RateRepositoryInterface
{
    protected Model $model;

    public function __construct(ProductRating $model)
    {
        $this->model = $model;
    }
}

