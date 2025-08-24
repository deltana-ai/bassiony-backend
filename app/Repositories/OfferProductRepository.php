<?php

namespace App\Repositories;

use App\Interfaces\OfferProductRepositoryInterface;
use App\Models\Offer;

use Illuminate\Database\Eloquent\Model;

class OfferProductRepository extends CrudRepository implements OfferProductRepositoryInterface
{
    protected Model $model;

    public function __construct(Offer $model)
    {
        $this->model = $model;
    }
}

