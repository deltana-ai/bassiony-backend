<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
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
         'name' => $this->name,
         'showHome' => $this->show_home,
         'position' => $this->position,
         'active' => $this->active,
         'imageUrl' => $this->getFirstMediaUrlTeam(),
         'image' => new MediaResource($this->getFirstMedia()),
     ];
    }
}
