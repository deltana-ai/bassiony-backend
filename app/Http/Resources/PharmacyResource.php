<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name ?? null,
            'address'        => $this->address ?? null,
            'phone'        => $this->phone ?? null,
            'license_number'  => $this->license_number ?? null,
            'imageUrl'    => $this->getFirstMediaUrl(),
            'image'       => new MediaResource($this->getFirstMedia()),
            'avg_rate'    => round($this->ratings_av_rate,1),
            'total_rate'  => $this->ratings_count ?? null,
        ];
    }
}
