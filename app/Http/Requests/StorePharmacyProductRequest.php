<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePharmacyProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
     return [
        'pharmacy_id' => ['required', 'exists:pharmacies,id'],
        'product_id'  => ['required', 'exists:products,id'],
        'price'       => ['required', 'numeric', 'min:0'],
        'stock'       => ['required', 'integer', 'min:0'],
        'expiry_date' => ['nullable', 'date', 'after:today'], // لو عايز تاريخ انتهاء يكون بعد النهاردة
    ];
    }
}
