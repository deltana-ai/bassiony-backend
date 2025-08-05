<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
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
 public function rules()
{
    return [
      'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'discount_percentage' => 'required|numeric|min:0|max:100',
        'start_date' => 'required|date|before_or_equal:end_date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'products' => 'required|array|min:1',
        'products.*' => 'exists:products,id',
    ];
}

}
