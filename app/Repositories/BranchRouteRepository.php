<?php
namespace App\Repositories;

use App\Interfaces\BranchRouteRepositoryInterface;
use App\Models\BranchRoute;
class BranchRouteRepository extends CrudRepository implements BranchRouteRepositoryInterface
{
    public function __construct(BranchRoute $model)
    {
        $this->model = $model;
    }
}