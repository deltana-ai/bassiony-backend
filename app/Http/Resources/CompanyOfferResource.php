<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'offer_type' => $this->offer_type,
            "discount" => $this->discount,
            'get_free_quantity' => $this->get_free_quantity,
            'max_redemption_per_invoice' => $this->max_redemption_per_invoice,
            
            "min_quantity" => $this->min_quantity,
            "total_quantity" => $this->total_quantity,
            "description" => $this->description,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "active" => (bool)$this->active,
            'company'=> $this->company?->name,
            'product'=> [
                    "id" => $this->product?->id,
                    "name" =>$this->product?->name,
                    "bar_code" =>$this->product?->name,
            ],
            'deleted' => isset($this->deleted_at),
            'deletedAt' => $this->deleted_at ? $this->deleted_at->format('d M, Y - H:i A') : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null,
            'sentSince' => $this->created_at ? $this->created_at->diffForHumans() : null,
            'createdAt' => $this->created_at ? $this->created_at->format('F d, Y - h:i A') : null,
        ];
    }
}
