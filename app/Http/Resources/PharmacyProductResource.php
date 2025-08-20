<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'price'       => $this->pivot->price,
            'stock'       => $this->pivot->stock,
            'pharmacy_id' => $this->pivot->pharmacy_id,
            'product_id'  => $this->pivot->product_id, 
        ];
    }
}
