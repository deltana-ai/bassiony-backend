<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRouteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'route_name' => 'string|max:100',
            'warehouse_id' => 'required|exists:warehouses,id',
            'locations' => 'nullable|array',
            'estimated_distance' => 'nullable|numeric',
            'estimated_duration' => 'nullable|integer',
            'base_shipping_cost' => 'numeric|min:0',
            'active' => 'boolean',
        ];
    }
}
