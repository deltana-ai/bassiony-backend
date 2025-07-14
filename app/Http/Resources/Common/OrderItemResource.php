<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      $originalPrice = $this->product->price ?? $this->price; 
      $discountValue = ($originalPrice - $this->price) * $this->quantity;

      return [
          'id' => $this->id,
          'productId' => $this->product_id,
          'productName' => $this->product->name ?? 'unknown',
          'quantity' => $this->quantity,
          'originalPrice' => $originalPrice,
          'productDiscount'=> $this->discount,
          'priceAfterDiscount' => $this->price,
          'discountValue' => $discountValue,
          'total' => $this->total,
      ];
    }
}
