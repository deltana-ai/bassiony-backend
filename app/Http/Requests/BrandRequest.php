<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class BrandRequest extends FormRequest
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
          'name' => ['required', 'string', 'max:90'],
          'position' => ['numeric','min:0'],
          'active' => ['boolean'],
          'show_home' => ['boolean'],
      ];

      if ($this->isMethod('post')) {
          $rules['name'][] = 'unique:brands,name';
      }
      else{
        $brand = $this->route('brand')?? $this->route('id');
        $rules['name'][] = Rule::unique('brands','name')->ignore($brand->id);
      }

      return $rules;
    }
}
