<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'category'    => $this->category,
            'brand'       => $this->brand?->name,
            'position'    => $this->position,
            'description' => $this->description,
            'active'      => (bool) $this->active,
            'show_home'   => (bool) $this->show_home,
            'rating'      => (float) $this->rating,
            'rating_count'=> $this->rating_count,
            'price'         => (float) $this->price,
            // 'pharmacy_product' =>  new PharmacyProductResource(
            //     $this->pharmacies->random()
            // ),

            'imageUrl'    => $this->getFirstMediaUrl(),
            'image'       => new MediaResource($this->getFirstMedia()),
            'deleted' => isset($this->deleted_at),
            'deletedAt' => $this->deleted_at ? $this->deleted_at->format('d M, Y - H:i A') : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null,
            'sentSince' => $this->created_at ? $this->created_at->diffForHumans() : null,
            'createdAt' => $this->created_at ? $this->created_at->format('F d, Y - h:i A') : null,];
    }
}
