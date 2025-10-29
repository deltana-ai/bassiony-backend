<?php

namespace App\Rules;

use App\Models\CompanyOffer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\ResponseOffer;

class UniqueOfferResponse implements ValidationRule
{
    protected int $companyOfferId;

    public function __construct(int $companyOfferId)
    {
        $this->companyOfferId = $companyOfferId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $offer = CompanyOffer::find($this->companyOfferId);

        if (! $offer || ! $offer->isCurrentlyActive()) {
            $fail('هذا العرض غير مفعل.');
            return;
        }

        $exists = ResponseOffer::where('company_offer_id', $this->companyOfferId)
            ->where('pharmacy_id', $value)
            ->whereIn('status', ['pending', 'approved', 'delivered','canceled'])
            ->exists();


        if ($exists) {
            $fail('تم استخدام هذا العرض بالفعل.');
        }
    }
}
