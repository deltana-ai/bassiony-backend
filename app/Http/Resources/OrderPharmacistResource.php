<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPharmacistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'total_price' => $this->total_price,
            'delivery_fee' => $this->delivery_fee,
            'created_at' => $this->created_at?->format('Y-m-d H:i'),

            // ✅ بيانات العميل
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ] : null,

            // ✅ بيانات الصيدلي الذي نفذ الطلب
            'pharmacist' => $this->pharmacist ? [
                'id' => $this->pharmacist->id,
                'name' => $this->pharmacist->name,
                'phone' => $this->pharmacist->phone,
            ] : null,

            // ✅ بيانات الصيدلية
            'pharmacy' => [
                'id' => $this->pharmacy->id,
                'name' => $this->pharmacy->name,
                'address' => $this->pharmacy->address,
            ],

            // ✅ عناصر الطلب
            'items' => OrderItemResource::collection($this->items),
        ];
    }
}
