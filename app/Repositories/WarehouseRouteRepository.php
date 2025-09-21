<?php
namespace App\Repositories;
use App\Interfaces\WarehouseRouteRepositoryInterface;
use App\Models\WarehouseRoute;
class WarehouseRouteRepository extends CrudRepository implements WarehouseRouteRepositoryInterface
{
    public function __construct(WarehouseRoute $model)
    {
        $this->model = $model;
    }
}