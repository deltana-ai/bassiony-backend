<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyRatingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'pharmacy_id'    => $this->pharmacy_id,
            'rating'         => $this->rating,
            'comment'        => $this->comment, 
        ];
    }
}
