<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

      return [
          'id' => $this->id,
          'productId' => $this->product_id,
          'productName' => $this->pharmacyProduct->product->name,
          'pharmacyId' => $this->pharmacyProduct->pharmacy_id,
          'pharmacyName' => $this->pharmacyProduct->pharmacy->name,
          'price_before' => $this->pharmacyProduct->price,
          'priceA_after' => $this->pharmacyProduct->priceAfterOffer(),
          'quantity' => $this->quantity,
          'total' => $this->total,
          'createdAt' => $this->created_at ? $this->created_at->format('Y-M-d H:i:s A') : null,
          'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null
      ];

    }
}
