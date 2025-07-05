<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'address'   => $this->address,
            'phone'     => $this->phone,
            'imageUrl' => $this->getFirstMediaUrlTeam(),
            'image' => new MediaResource($this->getFirstMedia()),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),

            'products' => $this->whenLoaded('pharmacyProducts', function () {
                return $this->pharmacyProducts->map(function ($pp) {
                    return [
                        'id'          => $pp->product->id,
                        'name'        => $pp->product->name,
                        'description' => $pp->product->description,
                        'price'       => $pp->price,
                        'quantity'    => $pp->quantity,
                        'imageUrl' => $pp->product?->getFirstMediaUrl(),
                        'image'    => $pp->product?->getFirstMedia()
                            ? new MediaResource($pp->product->getFirstMedia())
                            : null,

                        'offer'       => $pp->offer ? [
                            'discount_price' => $pp->offer->discount_price,
                            'start_date'     => $pp->offer->start_date,
                            'end_date'       => $pp->offer->end_date,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
