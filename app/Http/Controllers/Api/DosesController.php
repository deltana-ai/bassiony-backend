<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineIntake;
use App\Models\MedicineSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DosesController extends Controller
{
    public function addMedicineWithSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'dosage_per_intake' => 'required|string',
            'times' => 'required|array|min:1',
            'times.*' => 'date_format:H:i',
            'days' => 'required|array|min:1',
            'days.*' => 'integer|between:1,7',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $imagePath = $request->hasFile('image')
                ? $request->file('image')->store('medicine_images', 'public')
                : null;

            $medicine = Medicine::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'notes' => $request->notes,
                'dosage_per_intake' => $request->dosage_per_intake,
                'image_path' => $imagePath,
            ]);

            $schedule = $medicine->schedules()->create([
                'times' => $request->times,
                'days' => $request->days,
            ]);

            $this->generateFutureIntakes($medicine, $schedule, 7); // أسبوع فقط

            return response()->json([
                'message' => 'تم إضافة الدواء والمواعيد بنجاح',
                'medicine' => $medicine,
                'schedule' => $schedule
            ], 201);
        });
    }

    protected function generateFutureIntakes(Medicine $medicine, MedicineSchedule $schedule, $days = 7)
    {
        $startDate = now()->startOfDay();
        $endDate = now()->addDays($days)->endOfDay();

        $existingIntakes = MedicineIntake::where('medicine_id', $medicine->id)
            ->where('schedule_id', $schedule->id)
            ->whereBetween('scheduled_time', [$startDate, $endDate])
            ->pluck('scheduled_time')
            ->map(fn($time) => Carbon::parse($time)->format('Y-m-d H:i'))
            ->toArray();

        $intakesToCreate = [];

        $date = $startDate->copy();
        while ($date <= $endDate) {
            $dayOfWeek = $date->dayOfWeekIso; // 1 = Monday, 7 = Sunday
            if (in_array($dayOfWeek, $schedule->days)) {
                foreach ($schedule->times as $time) {
                    $scheduled = $date->copy()->setTimeFromTimeString($time)->format('Y-m-d H:i');
                    if (!in_array($scheduled, $existingIntakes)) {
                        $intakesToCreate[] = [
                            'medicine_id' => $medicine->id,
                            'schedule_id' => $schedule->id,
                            'scheduled_time' => Carbon::createFromFormat('Y-m-d H:i', $scheduled),
                            'taken' => false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            }
            $date->addDay();
        }

        if (!empty($intakesToCreate)) {
            MedicineIntake::insert($intakesToCreate);
        }
    }

    public function markAsTaken($intakeId)
    {
        $intake = MedicineIntake::with(['schedule.medicine' => function ($query) {
            $query->where('user_id', auth()->id());
        }])->find($intakeId);

        if (!$intake || !$intake->schedule || !$intake->schedule->medicine) {
            return response()->json(['message' => 'الجرعة غير موجودة أو غير مصرح لك'], 404);
        }

        $intake->update([
            'taken' => true,
            'actual_time' => now(),
        ]);

        return response()->json([
            'message' => 'تم تسجيل الجرعة كمأخوذة',
            'intake' => [
                'id' => $intake->id,
                'scheduled_time' => $intake->scheduled_time,
                'actual_time' => $intake->actual_time,
                'medicine' => $intake->schedule->medicine->name
            ]
        ]);
    }

    public function continueNextWeek(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medicine_id' => 'required|exists:medicines,id',
            'continue' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $medicine = Medicine::with('schedules')
            ->where('id', $request->medicine_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$medicine) {
            return response()->json(['message' => 'الدواء غير موجود أو غير مصرح لك'], 404);
        }

        if ($request->continue) {
            foreach ($medicine->schedules as $schedule) {
                $this->generateFutureIntakes($medicine, $schedule, 7); // أسبوع إضافي فقط
            }
            return response()->json(['message' => 'تم تمديد الجرعات لأسبوع إضافي']);
        } else {
            MedicineIntake::where('medicine_id', $medicine->id)
                ->whereBetween('scheduled_time', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])
                ->delete();

            return response()->json(['message' => 'تم حذف الجرعات القديمة لهذا الأسبوع']);
        }
    }



    public function getDosesByDay(Request $request)
{
    $user = auth()->user();

    $day = $request->query('day');
    $date = $request->query('date') 
        ? Carbon::parse($request->query('date'))->startOfDay() 
        : now()->startOfDay();

    if ($day) {
        // لو مرر اليوم رقماً مثل 1 = الاثنين
        $day = intval($day);
    } else {
        $day = $date->dayOfWeekIso; // افتراضي يوم اليوم الحالي
    }

    $intakes = MedicineIntake::with('schedule.medicine')
        ->whereHas('schedule.medicine', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->whereDay('scheduled_time', '=', $date->day)
        ->whereMonth('scheduled_time', '=', $date->month)
        ->whereYear('scheduled_time', '=', $date->year)
        ->get();

    return response()->json([
        'date' => $date->toDateString(),
        'day' => $day,
        'intakes' => $intakes
    ]);
}




public function getAllDoses()
{
    $userId = auth()->id();

    $startOfWeek = now()->startOfWeek();
    $endOfWeek = now()->endOfWeek();

    $intakes = MedicineIntake::select([
            'id', 'medicine_id', 'schedule_id', 'scheduled_time', 'taken', 'actual_time'
        ])
        ->with([
            'schedule:id,medicine_id', 
            'schedule.medicine:id,name,dosage_per_intake,image_path'
        ])
        ->whereHas('schedule.medicine', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereBetween('scheduled_time', [$startOfWeek, $endOfWeek])
        ->orderBy('scheduled_time')
        ->get();

    return response()->json([
        'week_start' => $startOfWeek->toDateString(),
        'week_end' => $endOfWeek->toDateString(),
        'intakes' => $intakes
    ]);
}


}
