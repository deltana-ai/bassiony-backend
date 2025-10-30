<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description,
            'price'             => $this->price,
            'active'            => (bool) $this->active,
            'imageUrl'          => $this->getFirstMediaUrl(),
            'stock'             => $this->pivot->stock,
            'reserved_stock'    => $this->pivot->reserved_stock,
            'expiry_date'       => $this->pivot->expiry_date,
            'batch_number'      => $this->pivot->batch_number,


            'warehouse' => [
                'id' => $this->warehouse?->id,
                'name' => $this->warehouse?->name,
            ],


        ];
    }
}
