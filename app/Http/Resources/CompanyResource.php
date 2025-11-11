<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $owner = Employee::where('company_id', $this->id)->where('is_owner',1)->first();

         return [
         'id' => $this->id,
         'name' => $this->name,
         'address' => $this->address,
         'phone' => $this->phone,
         'owner_email' => $owner?->email ?? null,
         'createdAt' => $this->created_at ? $this->created_at->format('Y-M-d H:i:s A') : null,
         'updatedAt' => $this->updated_at ? $this->updated_at->format('Y-M-d H:i:s A') : null,
         'deletedAt' => $this->deleted_at ? $this->deleted_at->format('Y-M-d H:i:s A') : null,
         'deleted' => isset($this->deleted_at),
       ];
    }
}
