<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PillReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'time' => 'required|date_format:H:i',
            'repeat' => 'boolean',
            'days' => 'nullable|array',
            'days.*'      => 'in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        ];
    }
}