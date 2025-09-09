<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'pharmacist_id' => 'nullable|exists:pharmacists,id',
        ];
    }
}
