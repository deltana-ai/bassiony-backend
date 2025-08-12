<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
            'text' => $this->text,
            'description' => $this->description,
            'position' => $this->position,
            'active' => $this->active,
            'button' => $this->button,
            'button_link' => $this->button_link,
            'imageUrl' => $this->getFirstMediaUrl(),
            'image' => $this->getFirstMediaResource(),
        ];
    }
}


