<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name'        => 'required|string|max:255',
            'position'    => 'nullable|integer',
            'active'      => 'boolean',
            'show_home'   => 'boolean',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'rating'      => 'nullable|numeric|min:0|max:9.9',
            'tax'         => 'nullable|numeric|min:0|max:999.99',
        ];
    }
}
