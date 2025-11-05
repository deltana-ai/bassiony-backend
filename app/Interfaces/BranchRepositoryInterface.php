<?php
namespace App\Interfaces;

use App\Interfaces\Interfaces\ICrudRepository;

interface BranchRepositoryInterface extends ICrudRepository
{
    public function getBranchProducts(int $branchId);

    public function getProductBatches(int $productId, int $branchId);
}
