<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyOfferRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'offer_type' => 'required|in:DISCOUNT,BUY_X_GET_Y',

            // Discount
            'discount' => 'required_if:offer_type,DISCOUNT|numeric|min:1|max:100',

            // Buy X Get Y
            'get_free_quantity' => 'required_if:offer_type,BUY_X_GET_Y|integer|min:1',
            'max_redemption_per_invoice' => 'nullable|integer|min:1',

            // Shared field
            'min_quantity' => 'required|integer|min:1', // BUY X OR MINIMUM FOR DISCOUNT

            'total_quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

       
    }
}
