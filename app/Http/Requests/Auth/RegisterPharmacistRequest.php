<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPharmacistRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','email','max:255','unique:pharmacists,email'],
            'phone'                 => ['required','string','unique:pharmacists,phone'],
            'password'              => ['required','string','min:6','confirmed'],
            // لازم تبعت حقل: password_confirmation
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'الاسم مطلوب',
            'email.required'        => 'البريد الإلكتروني مطلوب',
            'email.email'           => 'صيغة البريد غير صحيحة',
            'email.unique'          => 'البريد مسجّل من قبل',
            'phone.required'        => 'رقم الهاتف مطلوب',
            'phone.unique'          => 'رقم الهاتف مسجّل من قبل',
            'password.required'     => 'كلمة المرور مطلوبة',
            'password.min'          => 'كلمة المرور يجب ألا تقل عن 6 أحرف',
            'password.confirmed'    => 'تأكيد كلمة المرور غير مطابق',
        ];
    }
}
