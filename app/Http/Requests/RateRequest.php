<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
             'rating'     => 'required|numeric|min:1|max:5',
                'comment'    => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'حقل المستخدم مطلوب',
            'user_id.exists' => 'المستخدم غير موجود',
            'product_id.required' => 'حقل المنتج مطلوب',
            'product_id.exists' => 'المنتج غير موجود',
        ];
    }
}