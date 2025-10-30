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
            'pharmacy_id' => ['required', 'exists:pharmacies,id'],
            'location' => ['required', 'string', 'max:150'],
            'address'     => ['required', 'string', 'max:500'],
            'active'      => ['nullable', 'boolean'],
        ];
         if ($this->isMethod('post')) {
          $rules['name'][] = 'unique:branches,name';
        }
        else{
            $branch = $this->route('branch')?? $this->route('id');
            $rules['name'][] = Rule::unique('branches','name')->ignore($branch->id);

        }
        return $rules;
    }
}
