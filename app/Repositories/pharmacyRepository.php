<?php

namespace App\Repositories;

use App\Interfaces\PharmacyRepositoryInterface;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Model;

class PharmacyRepository extends CrudRepository implements PharmacyRepositoryInterface
{
    protected Model $model;

    public function __construct(Pharmacy $model)
    {
        $this->model = $model;
    }
}
