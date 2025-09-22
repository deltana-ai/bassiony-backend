<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Dashboard\PharmacyResource;
class BranchResource extends JsonResource
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
         'code' => $this->code,
      
         'pharmacy' => [
            'id' => $this->pharmacy->id ?? null,
            'name' => $this->pharmacy->name ?? null,
         ],
         'location' => [
            'id' => $this->location->id ?? null,
             'name' => $this->location->name ?? null,
        ],
         'createdAt' => $this->created_at ? $this->created_at->format('Y-M-d H:i:s A') : null,
         'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null,
         'deletedAt' => $this->deleted_at ? $this->deleted_at->format('Y-M-d H:i:s A') : null,
         'deleted' => isset($this->deleted_at),
       ];
    }
}
