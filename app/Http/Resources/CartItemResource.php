<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'quantity' => (int) $this->quantity,
            'product'  => new ProductResource($this->whenLoaded('product')), // nested resource
            'createdAt' => $this->created_at?->format('F d, Y - h:i A'),
            'updatedAt' => $this->updated_at?->format('F d, Y - h:i A'),
        ];
    }
}
