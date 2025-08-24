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
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'pharmacist_id' => 'nullable|exists:pharmacists,id'
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'حقل المستخدم مطلوب',
            'user_id.exists' => 'المستخدم غير موجود',
            'product_id.required' => 'حقل المنتج مطلوب',
            'product_id.exists' => 'المنتج غير موجود',
            'pharmacist_id.exists' => 'الصيدلي غير موجود'
        ];
    }
}