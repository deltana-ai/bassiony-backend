<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchProductRequest extends FormRequest
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
            'branch_id' => 'required|exists:branches,id',
            'pharmacy_product_id' => 'required|exists:pharmacy_product,id',
            'stock' => 'integer|min:0',
            'reserved_stock' => 'integer|min:0',
            'expiry_date' => 'nullable|date',
        ];
    }
}
