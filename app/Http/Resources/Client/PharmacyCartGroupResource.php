<?php
namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Client\CartItemResource;
class PharmacyCartGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pharmacyId' => $this->pharmacy_id,
            'pharmacyName' => $this->pharmacy_name,
            'subtotal' => $this->subtotal,
            'items' => CartItemResource::collection($this->items),
        ];
    }
}
