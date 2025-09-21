<?php
namespace App\Repositories;

use App\Interfaces\BranchProductRepositoryInterface;
use App\Models\BranchProduct;
class BranchProductRepository extends CrudRepository implements BranchProductRepositoryInterface
{
    public function __construct(BranchProduct $model)
    {
        $this->model = $model;
    }
}