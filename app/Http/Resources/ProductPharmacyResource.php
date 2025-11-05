<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPharmacyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalStock = (int) $this->total_stock;
        $totalReservedStock = (int) $this->total_reserved_stock;
        $availableStock = $totalStock - $totalReservedStock;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'active' => (bool) $this->active,
            'category' => $this->category_name,
            'brand' => $this->brand_name,
            'total_stock' => $totalStock,
            'total_reserved_stock' => $totalReservedStock,
            'available_stock' => $availableStock,
            'total_batches' => (int) $this->total_batches,
            'total_branches' => (int) $this->total_branches,
        ];
    }
}
