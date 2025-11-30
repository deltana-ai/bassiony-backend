<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseProduct2Resource extends JsonResource
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
            'description' => $this->description,
            'scientific_name' => $this->scientific_name,
            'active_ingredients' => $this->active_ingredients,
            'dosage_form' => $this->dosage_form,
            'gtin' => $this->gtin,
            'bar_code' => $this->bar_code,
            
            
           // 'price' => (float) $this->price,
            'company_discount_percent' => (float) $this->company_discount_percent,
            'tax' => (float) $this->tax,
            'price_after_discount_without_tax' => (float) $this->price_after_discount_without_tax,
            'price_after_discount_with_tax' => (float) $this->price_after_discount_with_tax,
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
