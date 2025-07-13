<?php

namespace App\Http\Controllers\Client\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MedicationService;
use App\Helpers\JsonResponse;


class MedicationController extends Controller
{
        protected $service;

     public function __construct(MedicationService $service)
    {
        $this->service = $service;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $data = $request->validate([
            'name' => 'required|string',
            'note' => 'nullable|string',
            'dose_amount' => 'required|integer',
            'times_per_day' => 'required|integer',
            'times' => 'required|array',
            'days' => 'required|array',
        ]);

        // if ($request->hasFile('image')) {
        //     $data['image'] = $request->file('image')->store('medications', 'public');
        // }

        $med = $this->service->createMedication($data, auth()->id());

    return JsonResponse::respondSuccess('تمت الإضافة بنجاح', $med);
    }

    /**
     * Display the specified resource.
     */
    public function today()
    {
        $medications = $this->service->getTodayMedications(auth()->id());
    return JsonResponse::respondSuccess('جرعات اليوم', $medications);
    }

    /**
     * Update the specified resource in storage.
     */
    public function handleExpiration(Request $request, $id)
    {
        $data = $request->validate([
            'approve_delete' => 'required|boolean'
        ]);

        $med = $this->service->handleExpirationDecision($id, auth()->id(), $data['approve_delete']);

    return JsonResponse::respondSuccess('تم تسجيل القرار بنجاح', $med);
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
    $deleted = $this->service->deleteMedication($id, auth()->id());

    if ($deleted) {
        return JsonResponse::respondSuccess('تم حذف الجرعة بنجاح');
    }

    return JsonResponse::respondError('فشل في حذف الجرعة', 500);
}

public function search(Request $request)
{
    $search = $request->query('q');
    $day = $request->query('day'); // optional

    $results = $this->service->searchMedications(auth()->id(), $search, $day);

    return JsonResponse::respondSuccess('نتائج البحث', $results);
}
}
