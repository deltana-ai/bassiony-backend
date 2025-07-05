<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class OfferProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $offer = $this->offer;
        $priceBefore = $this->price;
        $priceAfter = $offer->discount_price;
        return [
            'product_id'    => $product->id,
            'name'          => $product->name,
            'description'   => $product->description,
            'price_before'  => $this->price,
            'price_after'   => $offer->discount_price,
            'discount_percentage' => round((($priceBefore - $priceAfter) / $priceBefore) * 100, 2),
            'offer_period'  => [
                'start_date' => Carbon::parse($offer->start_date)->format('Y-m-d'),
                'end_date'   => Carbon::parse($offer->end_date)->format('Y-m-d'),
            ],
            'imageUrl'      => $product?->getFirstMediaUrl(),
            'image'         => $product?->getFirstMedia()
                ? new MediaResource($product->getFirstMedia())
                : null,
        ];
    }
}
