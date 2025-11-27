<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class CompanyPriceRequest extends FormRequest
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
        $companyId =  Auth::guard("employees")->user()->company_id   ;
        $rules = [
            'product_id' => ['required', 'exists:products,id'],
            'discount_percent' => ['required','numeric','between:0,100', ],
        ];

        if ($this->isMethod('post')) {
            $rules['discount_percent'][] =  Rule::unique('company_prices')->where(function ($query) use ($companyId) {
                    return $query->where('product_id', $this->product_id)
                                ->where('company_id', $companyId)
                                ->where('discount_percent', $this->discount_percent);
                });
        }
        else{
            $companyPrice = $this->route('companyPrice')?? $this->route('id');
            $companyPriceId = is_object($companyPrice) ? $companyPrice->id : $companyPrice;

            $rules['discount_percent'][] =  Rule::unique('company_prices')->where(function ($query) use ($companyId) {
                    return $query->where('product_id', $this->product_id)
                                ->where('company_id', $companyId)
                                ->where('discount_percent', $this->discount_percent);
                })->ignore($companyPriceId);
        }

        return $rules;

    }
}
