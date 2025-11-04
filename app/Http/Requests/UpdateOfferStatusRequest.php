<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Models\Offer;
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

        ];

    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $offer = Offer::find($this->route('id')); // أو حسب اسم الـ route parameter
            if (!$offer) {
                throw ValidationException::withMessages([
                    'offer' => 'العرض غير موجود',
                ]);
            }

            $currentStatus = $offer->status;
            $newStatus = $this->status;

            $allowedTransitions = [
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
