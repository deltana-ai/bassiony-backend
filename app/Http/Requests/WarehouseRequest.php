<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
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
    
           
        $rules =  [
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50',     
            'company_id'  => 'required|exists:companies,id',
            'location_id' => 'required|exists:locations,id',
            'active'      => 'nullable|boolean',
        ];
        if ($this->isMethod('post')) {
          $rules['name'][] = 'unique:warehouses,name';
          $rules['code'][] = 'unique:warehouses,code';
        }
        else{
            $warehouse = $this->route('warehouse')?? $this->route('id');
            $rules['name'][] = Rule::unique('warehouses','name')->ignore($warehouse->id);
            $rules['code'][] = Rule::unique('warehouses','code')->ignore($warehouse->id);

        }
        return $rules;
    }
}
