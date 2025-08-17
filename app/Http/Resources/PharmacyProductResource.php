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
    'name'        => $this->product->name,
    'category'    => $this->product->category?->name,
    'brand'       => $this->product->brand?->name,
    'price'       => $this->price,
    'stock'       => $this->stock,
    'description' => $this->description,
    'active'      => (bool) $this->active,
    'imageUrl'    => $this->getFirstMediaUrl('products'),
    'image'       => new MediaResource($this->getFirstMedia('products')),
    'created_at'  => $this->created_at->format('Y-m-d'),
];

    }
}
