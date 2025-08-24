<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'discount_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id' 
        ];
    }

    public function messages()
    {
        return [
            'products.required' => 'يجب اختيار منتج واحد على الأقل',
            'products.*.exists' => 'أحد المنتجات المحددة غير موجود'
        ];
    }
}