<?php

namespace App\Http\Requests;

use App\Models\Pharmacist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PharmacyRequest extends FormRequest
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
            'name'           => ['required', 'string', 'max:255'],
            'address'        => ['nullable', 'string', 'max:500'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'email' => ['required','string', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],

        
        ];
        if ($this->isMethod('post')) {
          $rules['license_number'][] = 'unique:pharmacies,license_number';
          $rules['email'] []= 'unique:pharmacists,email';

        }
        else{
             
            $pharmacy = $this->route('pharmacy')?? $this->route('id');
            $pharmacist = Pharmacist::where('pharmacy_id', $pharmacy->id)->where('is_owner',1)->first();

            $rules['license_number'][] = Rule::unique('pharmacies','license_number')->ignore($pharmacy->id);
            $rules['email'] = Rule::unique('pharmacists','email')->ignore($pharmacist->id);

        }
        return $rules;
    }
}
