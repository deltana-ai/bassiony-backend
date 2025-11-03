<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:90'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
        if ($this->isMethod('post')) {
            $rules['name'][] = Rule::unique('roles','name')->where(function ($query) {
                return $query->where('guard_name', $this->guard_name);
            });
        }
        else{
            $role = $this->route('id')?? $this->route('id');
            $rules['name'][] = Rule::unique('roles','name')->where(function ($query) {
                return $query->where('guard_name', $this->guard_name);
            })->ignore($role);
        }

        return $rules;
    }
}
