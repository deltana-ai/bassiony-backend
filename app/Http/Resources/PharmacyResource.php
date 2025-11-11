<?php

namespace App\Http\Resources;

use App\Models\Pharmacist;
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
        $owner = Pharmacist::where('pharmacy_id', $this->id)->where('is_owner',1)->first();
        return [
            'id'          => $this->id,
            'name'        => $this->name ?? null,
            'address'        => $this->address ?? null,
            'phone'        => $this->phone ?? null,
            'license_number'  => $this->license_number ?? null,
            'owner_email' => $owner->email ?? null,
            'imageUrl'    => $this->getFirstMediaUrl(),
            'image'       => new MediaResource($this->getFirstMedia()),
            'avg_rate'    => round($this->ratings_av_rate,1),
            'total_rate'  => $this->ratings_count ?? null,
        ];
    }
}
