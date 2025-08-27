<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSimpleResource extends JsonResource
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
            'name'        => $this->name,
            'category'    => $this->category?->name,
            'brand'       => $this->brand?->name,
            'rating'      => (float) $this->rating,
            'rating_count'=> $this->rating_count,
            'price'        => (float) $this->price,
            'imageUrl'    => $this->getFirstMediaUrl(),
            'image'       => new MediaResource($this->getFirstMedia()),
      ];
    }
}
