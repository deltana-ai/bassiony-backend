<?php

namespace App\Http\Resources\Common;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
          'pharmacyId' => $this->pharmacy_id,
          'pharmacyName' => $this->pharmacy->name ?? 'unknown',
          'status' => $this->status,
          'totalBeforeDiscount' => $this->total + $this->order_discount,
          'discount' => $this->order_discount,
          'finalTotal' => $this->total,
          'paid_from_wallet' => $this->paid_from_wallet,
          'paid_by_card' => $this->paid_by_card,
          'is_paid' => $this->is_paid,
          'payment_type' => $this->payment_type,
          'due_date' => $this->due_date,
          'paid_amount' => $this->paid_amount,
          'remaining_amount' => $this->remaining_amount	,
          'createdAt' => $this->created_at?->format('Y-m-d H:i'),
          'items' => OrderItemResource::collection($this->items),
      ];
    }
}
