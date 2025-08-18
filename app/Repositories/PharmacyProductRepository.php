<?php

namespace App\Repositories;

use App\Interfaces\PharmacyProductRepositoryInterface;
use App\Models\PharmacyProduct;
use Illuminate\Database\Eloquent\Model;

class PharmacyProductRepository extends CrudRepository implements PharmacyProductRepositoryInterface
{
    protected Model $model;

    public function __construct(PharmacyProduct $model)
    {
        $this->model = $model;
    }
}
