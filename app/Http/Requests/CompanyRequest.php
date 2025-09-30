<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
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
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone'   => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
        ];
         if ($this->isMethod('post')) {
          $rules['name'] = $rules['name'].'|unique:companies,name';
        }
        else{
            $company = $this->route('company')?? $this->route('id');
            $rules['name'] = "$rules[name]|".Rule::unique('companies','name')->ignore($company->id);

        }
        return $rules;
    }
}
