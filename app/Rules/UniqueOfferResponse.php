<?php

namespace App\Rules;

use App\Models\CompanyOffer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\ResponseOffer;

class UniqueOfferResponse implements ValidationRule
{
    protected int $companyOfferId;

    protected ?int $responseOfferId;


    public function __construct(int $companyOfferId , ?int $responseOfferId = null)
    {
        $this->companyOfferId  = $companyOfferId;
        $this->responseOfferId = $responseOfferId;
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

        if ($value != auth()->user()->pharmacy_id) {
            $fail('هذه الصيدلية غير مطابقة لحسابك.');
            return;
        }

        $existsQuery = ResponseOffer::where('company_offer_id', $this->companyOfferId)
            ->where('pharmacy_id', $value)
            ->whereIn('status', ['pending']);

        // Ignore the current response offer if updating
        if ($this->responseOfferId != null) {
            $existsQuery->where('id', '!=', $this->responseOfferId);
        }

        $exists = $existsQuery->exists();
        
        if ($exists) {
            $fail('تم استخدام هذا العرض بالفعل.');
        }
    }
}
