<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            "id"=> $this->id,
            "pharmacy"=> $this->pharmacy? [
                "id" => $this->pharmacy->id,
                "name" =>$this->pharmacy->name,
            ]:null,
            "warehouse"=> $this->warehouse? [
                "id" => $this->warehouse->id,
                "name" =>$this->warehouse->name,
            ]:null,
            "total_price"=> $this->total_price ,
            "item_price"=>$this->item_price,
            "quantity"=>$this->quantity,
            "status"=> $this->status,
            "offer"=> $this->offer? new CompanyOfferResource($this->offer):null,
            'createdAt' => $this->created_at ? $this->created_at->format('Y-M-d H:i:s A') : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null,
            'deletedAt' => $this->deleted_at ? $this->deleted_at->format('Y-M-d H:i:s A') : null,
            'deleted' => isset($this->deleted_at),
        ];
    }
}
