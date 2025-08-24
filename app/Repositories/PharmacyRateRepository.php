<?php

namespace App\Repositories;

use App\Interfaces\PharmacyRateRepositoryInterface;
use App\Models\PharmacyRating;
use Illuminate\Database\Eloquent\Model;

class PharmacyRateRepository extends CrudRepository implements PharmacyRateRepositoryInterface
{
    protected Model $model;

    public function __construct(PharmacyRating $model)
    {
        $this->model = $model;
    }
}
