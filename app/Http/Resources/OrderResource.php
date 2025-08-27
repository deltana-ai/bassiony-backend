<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'address_id'    => $this->address_id,
            'promo_code_id' => $this->promo_code_id,
            'payment_method'=> $this->payment_method,
            'status'        => $this->status,
            'delivery_fee'  => $this->delivery_fee,
            'total_price'   => $this->total_price,
            'created_at'    => $this->created_at->toDateTimeString(),
            'items'         => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
