<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
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
            'name' => ['nullable','string','max:100'],
            'active' => ['boolean'],
            'email' => ['nullable', 'email'],
            'password' => ['nullable', 'min:6','confirmed', 'string'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],

        ];

        if ($this->isMethod('post')) {
            $rules['email'][] = 'unique:employees,email';
        }

        else{
            $employee = $this->route('employee')?? $this->route('id');
            $rules['email'][] = Rule::unique('employees','email')->ignore($employee->id);
        }
        return $rules;
    }
}
