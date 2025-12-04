<?php

namespace App\Http\Resources;

use App\Models\CompanyOffer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'cart_id' => $this->id,
            'total_items' => $this->total_items,
            'total_price' => round($this->total_price, 2),
            'items' => $this->items->map(function ($item)  {
            //    $offer = CompanyOffer::getActiveOffer($companyId, $item->product_id, $item->quantity);
                $offer = $item->offer;
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name_en ?? $item->product->name_ar,
                    'quantity' => $item->quantity,
                    'discount_percent' => $item->discount_percent,
                    'free_quantity' => $item->free_quantity,
                    'all_quantity' => $item->quantity + $item->free_quantity,
                    'unit_price' => round( $item->product->price),
                    'price' => round($item->unit_price, 2),
                    'total_price' => round($item->total_price, 2),
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name_en ?? $item->product->name_ar,
                    'offer' => $offer ?CompanyOfferResource::make($offer): null,
                ];
            }),
        ];
    }
}
