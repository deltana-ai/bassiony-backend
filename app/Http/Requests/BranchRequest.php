<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
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
        $rules = [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:50', 'unique:branches,code,' . $this->id],
            'pharmacy_id' => ['required', 'exists:pharmacies,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'address'     => ['required', 'string', 'max:500'],
            'active'      => ['nullable', 'boolean'],
        ];
         if ($this->isMethod('post')) {
          $rules['name'][] = 'unique:branches,name';
          $rules['code'][] = 'unique:branches,code';
        }
        else{
            $branch = $this->route('branch')?? $this->route('id');
            $rules['name'][] = Rule::unique('branches','name')->ignore($branch->id);
            $rules['code'][] = Rule::unique('branches','code')->ignore($branch->id);

        }
        return $rules;
    }
}
