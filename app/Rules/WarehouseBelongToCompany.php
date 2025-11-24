<?php

namespace App\Rules;

use App\Models\Warehouse;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
class WarehouseBelongToCompany implements ValidationRule
{

   

    public function __construct()
    {
        
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userCompnyId = Auth::user()->company_id ??null;
        if (is_array($value ) ) {
         
            $count = Warehouse::whereIn('id' , $value)->where('company_id',$userCompnyId )->count();
            if ($count !== count($value)) {
                $fail(' يوجد مخازن غير تابعة لشركتك');
            }
        }
        else{
            $warehouse = Warehouse::where('id' , $value)->where('company_id',$userCompnyId )->first();
            if (!$warehouse ) {
                $fail(' هذا المخزن غير تابع لشركتك');
            }
        }
    }
}
