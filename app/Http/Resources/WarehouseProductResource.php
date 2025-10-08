<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            
            'products' => $this->products->map(function ($product) {
                return [
                    'id'                => $product->id,
                    'name'              => $product->name,
                    'description'       => $product->description,
                    'price'             => $product->price,
                    'active'            => (bool) $product->active,
                    'imageUrl'          => $product->getFirstMediaUrl(),
                    'warehouse_price'   => $product->pivot->warehouse_price,
                    'stock'             => $product->pivot->stock,
                    'reserved_stock'    => $product->pivot->reserved_stock,
                    'expiry_date'       => $product->pivot->expiry_date,
                    'batch_number'      => $product->pivot->batch_number,
                ];
            }),
            'warehouse' => [
                'id' => $this->id,
                'name' => $this->name,
                'code' => $this->code,
            ],
            
            
        ];
    }
}
