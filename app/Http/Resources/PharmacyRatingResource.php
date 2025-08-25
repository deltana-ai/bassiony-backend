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
            'created_at'     => $this->created_at?$this->created_at->toIso8601String():null,
            'updated_at'     => $this->updated_at?$this->updated_at->toIso8601String():null,
            
            'user'           => $this->whenLoaded('user', function () {
                                return [
                                    'id'   => $this->user->id,
                                    'name' => $this->user->name,
                                ];
                              }),
            
            'pharmacy'        => $this->whenLoaded('pharmacy', function () {
                                return [
                                    'id'        => $this->pharmacy->id,
                                    'name'      => $this->pharmacy->name,
                                    'imageUrl'  => $this->pharmacy->getFirstMediaUrl(),
                                    'image'     => new MediaResource($this->pharmacy->getFirstMedia()),
                                ];
                              }),
        ];
    }
}
