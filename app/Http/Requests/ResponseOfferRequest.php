<?php

namespace App\Http\Requests;

use App\Models\CompanyOffer;
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
        $rules = [
            'company_offer_id' => ['required', 'exists:company_offers,id'],
            'pharmacy_id' => ['required', 'exists:pharmacies,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'times' => ['nullable', 'integer', 'min:1'],
        ];

        $offer = CompanyOffer::find($this->company_offer_id);

        if ($offer && $offer->offer_type === 'BUY_X_GET_Y') {
            $rules['times'] = ['required', 'integer', 'min:1'];
            $rules['quantity'] = ['nullable'];
        } else {
            $rules['quantity'] = ['required', 'integer', 'min:1'];
            $rules['times'] = ['nullable'];
        }



        if ($this->isMethod('post')) {

        $rules ['pharmacy_id'][] = new UniqueOfferResponse($this->company_offer_id);

        }
        else{
            $responseOfferId = $this->route('response_company_offer')?? $this->route('id'); 
            $rules ['pharmacy_id'][] = new UniqueOfferResponse($this->company_offer_id,$responseOfferId);

        }
        return $rules;
    }
}
