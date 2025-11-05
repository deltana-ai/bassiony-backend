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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id|unique:branch_product,product_id,NULL,id,branch_id,' . $this->branch_id,
            'branch_price' => 'nullable|numeric',
            'batch_number' => 'nullable|string',
            'stock' => 'integer|min:0',
            'reserved_stock' => 'integer|min:0',
            'expiry_date' => 'nullable|date|date_format:d-m-Y',
        ];
    }
}
