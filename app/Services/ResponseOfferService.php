<?php

namespace App\Services;

use App\Http\Requests\ResponseOfferRequest;
use App\Interfaces\ResponseOfferRepositoryInterface;
use App\Models\CompanyOffer;
use App\Models\ResponseOffer;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ResponseOfferService
{
    protected mixed $crudRepository;

    public function __construct(ResponseOfferRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    /**
     * Create (apply) a new response offer.
     *
     * For BUY_X_GET_Y: request must include 'times' => number of times to apply the X+Y group.
     * For DISCOUNT: request must include 'quantity'.
     */
    public function applyOffer(CompanyOffer $offer, ResponseOfferRequest $request): ResponseOffer
    {
        $data = $this->handleData($request, $offer);

        $responseOffer = $this->crudRepository->create($data);

        return $responseOffer;
    }

    /**
     * Update an existing response offer by id.
     */
    public function updateOffer(CompanyOffer $offer, ResponseOfferRequest $request, $id)
    {
        $responseOffer = $this->crudRepository->find($id);
        if (! $responseOffer) {
            throw ValidationException::withMessages(['response_offer' => 'العرض المطلوب غير موجود.']);
        }

        if ($responseOffer->status !== 'pending') {
            throw new \Exception("لا يمكن تعديل العرض بعد أن تغيّرت حالته.");
        }

        
        $companyOffer = CompanyOffer::findOrFail($offer->id);

        // Add back previously used units from the previous responseOffer (if any)
        $previousUsed = ($responseOffer->all_quantity ?? ($responseOffer->quantity + $responseOffer->total_free_quantity)) ?? 0;
       

        // Now compute new data and attempt to reserve
        $data = $this->handleData($request, $offer);

        // Update using repository (expect repository->update($data, $id) or similar)
        $this->crudRepository->update($data, $id);

        return $this->crudRepository->find($id);
    }

    /**
     * Build the data array, compute quantities & price, and reserve quantities on the offer.
     * Returns array ready to be passed to repository->create/update.
     */
    private function handleData(ResponseOfferRequest $request, CompanyOffer $offer): array
    {
        $validated = $request->validated();

        $data = $validated;
        $data['item_price'] = (float) $offer->product->price;
        $data['status'] = 'pending';

        // Default fields
        $data['total_free_quantity'] = 0;
        $data['all_quantity'] = 0;
        $data['total_price'] = 0;

        // Ensure offer is active
        if (!$offer->active ||now()->lt(Carbon::parse($offer->start_date)) || now()->gt(Carbon::parse($offer->end_date))) {

            throw new \Exception("هذا العرض غير متاح حالياً.");
        }

        if ($offer->offer_type === 'DISCOUNT') {
            // For discount we expect 'quantity' in request
            if (! isset($validated['quantity'])) {
                throw ValidationException::withMessages(['quantity' => 'مطلوب حقل الكمية لتطبيق الخصم.']);
            }

            $quantity = (int) $validated['quantity'];

            if ($quantity < $offer->min_quantity) {
                throw new \Exception("الحد الأدنى للكمية للحصول على العرض هو {$offer->min_quantity}.");
            }

            // Check total_quantity availability
            if ($offer->total_quantity < $quantity) {
                throw new \Exception("لا يوجد كمية كافية للعرض.");
            }


            $total = $quantity * $data['item_price'];
            $totalAfterDiscount = $total - ($total * ($offer->discount / 100));

            $data['quantity'] = $quantity;
            $data['total_free_quantity'] = 0;
            $data['all_quantity'] = $quantity;
            $data['total_price'] = round($totalAfterDiscount, 2);

            return $data;
        }

        // BUY_X_GET_Y
        if ($offer->offer_type === 'BUY_X_GET_Y') {
            // We expect 'times' from request: number of times to apply the X+Y group
            if (! isset($validated['times'])) {
                throw ValidationException::withMessages(['times' => 'مطلوب عدد مرات تطبيق العرض (times).']);
            }

            $requestedTimes = (int) $validated['times'];
            if ($requestedTimes < 1) {
                throw ValidationException::withMessages(['times' => 'عدد المرات يجب أن يكون رقمًا أكبر من أو يساوي 1.']);
            }

            $X = (int) $offer->min_quantity;
            $Y = (int) $offer->get_free_quantity;
            $M = $offer->max_redemption_per_invoice; // nullable

            // If M is defined as max free units per invoice, we must compute max times allowed by M:
            if ($M !== null) {
                // maximum times allowed so that freeUnits = times * Y <= M
                $maxTimesByM = intdiv((int) $M, max(1, $Y)); // avoid division by zero
                if ($maxTimesByM <= 0) {
                    throw new \Exception("العرض لا يسمح بمنح وحدات مجانية في هذه الفاتورة.");
                }
                $effectiveTimes = min($requestedTimes, $maxTimesByM);
            } else {
                $effectiveTimes = $requestedTimes;
            }

            // compute actual quantities
            $buyQty = $effectiveTimes * $X;
            $freeQty = $effectiveTimes * $Y;
            $totalUsed = $buyQty + $freeQty;

            // Check available total_quantity
            if ($offer->total_quantity < $totalUsed) {
                throw new \Exception("لا يوجد كمية كافية لتنفيذ هذا العدد من مرات العرض.");
            }


            // Price is only for bought quantity
            $totalPrice = $buyQty * $data['item_price'];

            // Fill data fields to store
            $data['times'] = $requestedTimes; // keep what user requested
            $data['quantity'] = $buyQty;      // actual paid quantity
            $data['total_free_quantity'] = $freeQty;
            $data['all_quantity'] = $totalUsed;
            $data['total_price'] = round($totalPrice, 2);

            return $data;
        }

        throw new \Exception("نوع العرض غير مدعوم.");
    }
}