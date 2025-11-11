<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
class CompanyRequest extends FormRequest
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
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone'   => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'email' => 'required|string|email',
            'password' => 'string|min:6|confirmed',
            

        ];
         if ($this->isMethod('post')) {
          $rules['name'] = $rules['name'].'|unique:companies,name';
          $rules['email'] = $rules['email'].'|unique:employees,email';
          $rules['password'] = $rules['password'].'|required';

        }
        else{
            $company = $this->route('company')?? $this->route('id');
            // if(Auth::guard('employees')->check()){ 
            //     $company = Company::find(auth("employees")->user()->company_id);
            // }
            $employee = Employee::where('company_id', $company->id)->where('is_owner',1)->first();
            
            $rules['name'] = "$rules[name]|".Rule::unique('companies','name')->ignore($company->id);
            $rules['email'] = "$rules[email]|".Rule::unique('employees','email')->ignore($employee->id);
            $rules['password'] = $rules['password'].'|nullable';
        }
        return $rules;
    }
}
