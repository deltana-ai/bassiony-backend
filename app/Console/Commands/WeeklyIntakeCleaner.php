<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Medicine;
use App\Models\MedicineIntake;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckWeeklyIntake extends Command
{
    protected $signature = 'medicines:check-weekly';
    protected $description = 'Check weekly intake continuation for all users';

    public function handle()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $medicines = Medicine::with(['schedules'])->get();

        foreach ($medicines as $medicine) {
            $hasIntakes = MedicineIntake::where('medicine_id', $medicine->id)
                ->whereBetween('scheduled_time', [$startOfWeek, $endOfWeek])
                ->exists();

            if ($hasIntakes) {
                // هنا في التطبيق الحقيقي تقدر تبعت إشعار أو بريد إلكتروني للمستخدم
                // أو تسجل الطلب في جدول pending_intakes

                Log::info("Medicine {$medicine->id} needs user confirmation to continue next week.");

                // في التطبيق، انتظر موافقة المستخدم، ولو مفيش رد احذف القديم
                // هنا افترضنا انه لم يرد، نحذف:
                $expired = MedicineIntake::where('medicine_id', $medicine->id)
                    ->whereBetween('scheduled_time', [$startOfWeek, $endOfWeek])
                    ->delete();

                Log::info("Deleted $expired old intakes for medicine {$medicine->id}");
            }
        }

        return Command::SUCCESS;
    }
}
