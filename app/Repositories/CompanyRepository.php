<?php

namespace App\Repositories;

use App\Interfaces\CompanyRepositoryInterface;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyRepository extends CrudRepository implements CompanyRepositoryInterface
{
    protected Model $model;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }
}
