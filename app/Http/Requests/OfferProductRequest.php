<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'offer_id' => 'required|exists:offers,id',
            'product_id' => 'required|exists:products,id',
        ];
    }
}