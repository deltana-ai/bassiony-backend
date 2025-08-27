<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
     public function authorize(): bool
    {
        return true; // لو فيه صلاحيات تقدر تتحكم هنا
    }

   public function rules(): array
    {
        return [
            'address_id'     => 'nullable|exists:addresses,id',
            'promo_code_id'  => 'nullable|exists:promo_codes,id',
            'payment_method' => 'nullable|in:cash,card,insurance',
            'delivery_fee'   => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.exists'     => 'The selected address is invalid.',
            'promo_code_id.exists'  => 'The selected promo code is invalid.',
            'payment_method.in'     => 'Payment method must be either cash, card, or insurance.',
            'delivery_fee.numeric'  => 'Delivery fee must be a number.',
            'delivery_fee.min'      => 'Delivery fee must be greater than or equal to 0.',
        ];
    }
}
