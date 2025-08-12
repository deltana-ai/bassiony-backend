<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactUsRequest extends FormRequest
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
        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            return [
                'name' => 'nullable',
                'email' => ['nullable'],
                'phone' => ['nullable'],
                'address' => ['nullable'],
                'subject' => ['nullable'],
                'message' => ['nullable'],
            ];
        }

        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['nullable'],
            'address' => ['nullable'],
            'subject' => ['nullable'],
            'message' => ['nullable'],
        ];
    }
}
