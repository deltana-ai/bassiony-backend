<?php
namespace App\Repositories;

use App\Interfaces\CompanyOfferRepositoryInterface;
use App\Models\CompanyOffer;
class CompanyOfferRepository extends CrudRepository implements CompanyOfferRepositoryInterface
{
    public function __construct(CompanyOffer $model)
    {
        $this->model = $model;
    }
}