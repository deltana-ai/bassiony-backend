<?php

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'active' => (bool)$this->active,
            'role' => $this->getRoleNames()->first(),
            'role_id'=> $this->roles->first()?->id,
            'company' => new CompanyResource($this->company),
            // 'warehouses' => $this->warehouses? WarehouseResource::collection($this->warehouses):null,
            'warehouses' => $this->warehouses ? WarehouseResource::collection($this->warehouses) : null,
            'warehouse_id' => $this->warehouses->first()?->id,
            'warehouse_name' => $this->warehouses->first()?->name,
            'address' => $this->address,
            'createdAt' => $this->created_at ? $this->created_at->format('d-M-Y H:i A') : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->format('d-M-Y H:i A') : null
        ];
    }
}
