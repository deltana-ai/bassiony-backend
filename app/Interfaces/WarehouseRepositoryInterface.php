<?php
namespace App\Interfaces;

use App\Interfaces\Interfaces\ICrudRepository;

interface WarehouseRepositoryInterface extends ICrudRepository
{
        public function getWarehouseProducts(int $warehouseId);
        public function getProductBatches(int $productId, int $warehouseId);

}
