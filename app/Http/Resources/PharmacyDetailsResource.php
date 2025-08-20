<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyDetailsResource extends JsonResource
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
            'name'           => $this->name,
            'phone'          => $this->phone,
            'address'        => $this->address,
            'license_number' => $this->license_number,
            'imageUrl'       => $this->getFirstMediaUrl(),
            'image'          => new MediaResource($this->getFirstMedia()),
            'panner_images'   => MediaResource::collection($this->getAllMedia()),
            'avg_rate'       => round($this->ratings_av_rate,1),
            'total_rate'     => $this->ratings_count,
        ];
    }
}
