<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PillReminderRequest;
use App\Http\Resources\Dashboard\PillReminderResource;
use App\Interfaces\PillReminderRepositoryInterface;
use Illuminate\Http\Request;

class PillReminderController extends Controller
{
    protected $pillReminderRepository;

    public function __construct(PillReminderRepositoryInterface $pillReminderRepository)
    {
        $this->pillReminderRepository = $pillReminderRepository;
    }

    /**
     * عرض كل التذكيرات الخاصة باليوزر
     */
    public function index()
    {
        $reminders = $this->pillReminderRepository->all();
        return PillReminderResource::collection($reminders);
    }

    /**
     * إنشاء تذكير جديد
     */
    public function store(PillReminderRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id(); // ربط التذكير بالمستخدم

        $reminder = $this->pillReminderRepository->create($data);

        return response()->json([
            'message' => 'تمت إضافة التذكير بنجاح',
            'data'    => new PillReminderResource($reminder)
        ], 201);
    }

    /**
     * عرض تذكير محدد
     */
    public function show($id)
    {
        $reminder = $this->pillReminderRepository->findById($id);
        return new PillReminderResource($reminder);
    }

    /**
     * تعديل تذكير
     */
    public function update(PillReminderRequest $request, $id)
    {
        $data = $request->validated();

        $reminder = $this->pillReminderRepository->update($id, $data);

        return response()->json([
            'message' => 'تم تحديث التذكير بنجاح',
            'data'    => new PillReminderResource($reminder)
        ]);
    }

    /**
     * حذف تذكير
     */
    public function destroy($id)
    {
        $this->pillReminderRepository->delete($id);

        return response()->json([
            'message' => 'تم حذف التذكير بنجاح'
        ]);
    }

    /**
     * جدول التذكيرات حسب التاريخ
     */
    public function schedule(Request $request)
    {
        //date format: Y-m-d
        $date = $request->query('date', now()->toDateString());

        $reminders = $this->pillReminderRepository->all()
            ->filter(function ($reminder) use ($date) {
                return $date >= $reminder->start_date 
                    && (!$reminder->end_date || $date <= $reminder->end_date)
                    && in_array(strtolower(date('l', strtotime($date))), $reminder->days ?? []);
            });

        return response()->json([
            'date'      => $date,
            'reminders' => PillReminderResource::collection($reminders)
        ]);
    }
}
