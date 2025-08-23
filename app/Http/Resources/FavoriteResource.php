<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'pharmacist_id' => $this->pharmacist_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            'user' => new UserResource($this->whenLoaded('user')),
            
            'product' => new ProductResource($this->getProduct()),
            
            // 'pharmacist' => new PharmacistResource($this->whenLoaded('pharmacist')),
        ];
    }
    
    protected function getProduct()
    {
        if ($this->relationLoaded('product')) {
            return $this->product;
        }
        
        return \App\Models\Product::find($this->product_id);
    }
}