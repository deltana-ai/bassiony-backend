<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchProduct2Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalStock = (int) $this->total_stock;
        $reservedStock = (int) $this->reserved_stock;
        $availableStock = $totalStock - $reservedStock;
         return [
            'id' => $this->id,
            'name' => $this->name,
            'scientific_name' => $this->scientific_name,
            'active_ingredients' => $this->active_ingredients,
            'dosage_form' => $this->dosage_form,
             'gtin' => $this->gtin,
            'bar_code' => $this->bar_code,
            'description' => $this->description,
            'price' => (float) $this->price,
            'tax' => (float) $this->tax,
            'price_without_tax' => (float) $this->price_without_tax,
            'active' => (bool) $this->active,
            'imageUrl'          => $this->getFirstMediaUrl(),
            'total_stock' => $totalStock,
            'reserved_stock' => $reservedStock,
            'available_stock' => $availableStock,
            'total_batches' => (int) $this->total_batches,
            'stock_status' => $this->getStockStatus($availableStock),
        ];

    }

    protected function getStockStatus(int $availableStock): string
    {
        if ($availableStock <= 0) {
            return 'out_of_stock';
        } elseif ($availableStock <= 10) {
            return 'low_stock';
        } elseif ($availableStock <= 50) {
            return 'medium_stock';
        } else {
            return 'in_stock';
        }
    }
       
}
