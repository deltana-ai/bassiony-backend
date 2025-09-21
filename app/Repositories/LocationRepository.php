<?php

namespace App\Repositories;

use App\Interfaces\LocationRepositoryInterface;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;

class LocationRepository extends CrudRepository implements LocationRepositoryInterface
{
    protected Model $model;

    public function __construct(Location $model)
    {
        $this->model = $model;
    }
}
