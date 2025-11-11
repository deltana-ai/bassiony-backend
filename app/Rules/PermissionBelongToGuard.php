<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionBelongToGuard implements ValidationRule
{

   
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user_guard = collect(array_keys(config('auth.guards')))->first(fn($g)=> Auth::guard($g)->check());
        $values = is_array($value)?$value :[$value];
         if (is_array($value ) ) {
         
            $count = Permission::whereIn('id' , $values)->where('guard_name',$user_guard )->count();
            if ($count !== count($value)) {
                $fail(' بعض الصلاحيات المحددة لا تتوافق مع المستخدم');
            }
        }
    }
}
