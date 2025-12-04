<?php

namespace App\Repositories;

use App\Interfaces\PharmacyOrderRepositoryInterface;
use App\Models\PharmacyOrder;

use Illuminate\Database\Eloquent\Model;

class PharmacyOrderRepository extends CrudRepository implements PharmacyOrderRepositoryInterface
{
    protected Model $model;

    public function __construct(PharmacyOrder $model)
    {
        $this->model = $model;
    }
}
