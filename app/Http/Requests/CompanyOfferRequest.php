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
        
        if ($this->isMethod('post')) {
            return [
                'warehouse_product_id' => ['required', 'exists:warehouse_product,id'],
                'discount' => ['required', 'numeric', 'min:0'],
                'active' => ['boolean'],
                'min_quantity' => ['required', 'integer', 'min:1'],
                'total_quantity' => ['required', 'integer', 'min:1'],

                'description' => ['nullable', 'string'],
                'start_date' => ['required', 'date','date_format:d-m-Y'],
                'end_date' => ['required', 'date','date_format:d-m-Y', 'after_or_equal:start_date']
            ];
        }
        else{
            
            return [
                'warehouse_product_id' => ['nullable', 'exists:warehouse_product,id'],
                'discount' => ['nullable', 'numeric', 'min:0'],
                'active' => ['nullable','boolean'],
                'min_quantity' => ['nullable', 'integer', 'min:1'],
                'total_quantity' => ['nullable', 'integer', 'min:1'],

                'description' => ['nullable', 'string'],
                'start_date' => ['nullable', 'date','date_format:d-m-Y'],
                'end_date' => ['nullable', 'date','date_format:d-m-Y', 'after_or_equal:start_date']
            ];
        
        }

       
    }
}
