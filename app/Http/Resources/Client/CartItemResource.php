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
      $pharmacy_product = $this->pharmacyProduct;
      return [
          'product_id'     => $this->pharmacy_product_id,
          'name'           => $pharmacy_product->product->name,
          'base_price'     => round($this->base_price,2),
          'price_after_discount' => round($this->price_after_discount,2),
          'tax_amount'     => round($this->tax_amount,2),
          'final_price'    => round( $this->final_price,2),
          'quantity'       => $this->quantity,
          'total'          => round($this->total,2),
          'offer_id'       => $pharmacy_product->offer?->id ,
          'createdAt' => $this->created_at ? $this->created_at->format('Y-M-d H:i:s A') : null,
          'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null
      ];

    }
}
