<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PharmacistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $pharmacistId = $this->route('pharmacist')?->id;

        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pharmacists', 'email')->ignore($pharmacistId),
            ],
            'password' => $this->isMethod('post') ? 'required|min:6' : 'nullable|min:6',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
        ];
    }
}




