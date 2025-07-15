<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Client\PharmacyCartGroupResource;

class CartResource extends JsonResource
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
          'countItems' => $this->countCart(),
          'total' => $this->subtotal,
          'pharmacies' => PharmacyCartGroupResource::collection($this->groupCartItemsByPharmacy()),
          'createdAt' => $this->created_at ? $this->created_at->format('Y-M-d H:i:s A') : null,
          'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null
      ];
    }
}
