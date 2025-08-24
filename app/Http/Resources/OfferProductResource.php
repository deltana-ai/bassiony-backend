<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price ?? 0, 
            'category' => $this->category->name ?? null,
            'brand' => $this->brand->name ?? null,
            'rating' => (float) $this->rating,
            'image' => $this->getFirstMediaUrl('image') 
        ];
    }
}