<?php
namespace App\Repositories;

use App\Interfaces\CompanyProductRepositoryInterface;
use App\Models\CompanyProduct;
class CompanyProductRepository extends CrudRepository implements CompanyProductRepositoryInterface
{
    public function __construct(CompanyProduct $model)
    {
        $this->model = $model;
    }
}