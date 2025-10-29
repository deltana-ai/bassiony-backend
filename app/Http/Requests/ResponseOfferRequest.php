<?php

namespace App\Http\Requests;

use App\Rules\UniqueOfferResponse;
use Illuminate\Foundation\Http\FormRequest;

class ResponseOfferRequest extends FormRequest
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
            'company_offer_id' => ['required', 'exists:company_offers,id'],
            'pharmacy_id' => ['required', 'exists:pharmacies,id',  new UniqueOfferResponse($this->company_offer_id)],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
