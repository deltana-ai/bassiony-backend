<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'active' => $this->active ?? null,
            'imageUrl' => $this->getFirstMediaUrl(),
            'image' => new MediaResource($this->getFirstMedia()) ,
            'createdAt' => $this->created_at ? $this->created_at->format('d-M-Y H:i A') : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->format('d-M-Y H:i A') : null,
        ];
    }
}


