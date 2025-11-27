<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'discount_percent' => $this->discount_percent,
            'final_price' => $this->company_sell_price,
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null,
            'sentSince' => $this->created_at ? $this->created_at->diffForHumans() : null,
            'createdAt' => $this->created_at ? $this->created_at->format('F d, Y - h:i A') : null,
        ];
    }
}
