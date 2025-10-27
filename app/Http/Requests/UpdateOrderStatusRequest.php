<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:pending,approved,rejected,delivered',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Order status is required.',
            'status.in'       => 'Invalid order status provided.',
        ];
    }
}
