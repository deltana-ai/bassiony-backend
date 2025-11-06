<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Models\ResponseOffer;
class UpdateOfferStatusRequest extends FormRequest
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
        return [
            'status' => ['required', 'in:pending,approved,rejected,delivered,returned,completed,canceled'],
            'warehouse_id'=> ['required','exists:warehouses,id','required_if:status,approved'],
        ];

    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $offerId = $this->route('id') ?? $this->id;
            $offer = ResponseOffer::find($offerId); // أو حسب اسم الـ route parameter
            if (!$offer) {
                throw ValidationException::withMessages([
                    'offer' => 'العرض غير موجود',
                ]);
            }

            $currentStatus = $offer->status;
            $newStatus = $this->status;
logger(['current' => $currentStatus, 'new' => $offer]);

            $allowedTransitions = [
                  null        => ['pending', 'approved'], // الحالة المبدئية
                'pending'   => ['approved', 'rejected'],
                'approved'  => ['delivered', 'canceled'],
                'delivered' => ['returned', 'completed'],
                'returned'  => ['delivered', 'canceled'],
                'completed' => [],
                'rejected'  => [],
                'canceled'  => [],
            ];

            if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
                $validator->errors()->add(
                    'status',
                    "لا يمكن تغيير الحالة من $currentStatus إلى $newStatus"
                );
            }
        });
    }
}
