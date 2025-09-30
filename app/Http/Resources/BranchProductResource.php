<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'products' => $this->products->map(function ($product) {
                return [
                    'id'             => $product->id,
                    'name'           => $product->name,
                    'description'    => $product->description,
                    'price'          => $product->price,
                    'active'         => (bool) $product->active,
                    'imageUrl'       => $product->getFirstMediaUrl(),
                    'branch_price'   => $product->pivot->branch_price,
                    'stock'          => $product->pivot->stock,
                    'reserved_stock' => $product->pivot->reserved_stock,
                    'expiry_date'    => $product->pivot->expiry_date,
                    'batch_number'   => $product->pivot->batch_number,
                ];
            }),
            'pharmacy' => new PharmacyResource($this->pharmacy), 
        ];

    }
}
