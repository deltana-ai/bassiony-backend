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
                

                Log::info("Medicine {$medicine->id} needs user confirmation to continue next week.");

           
                $expired = MedicineIntake::where('medicine_id', $medicine->id)
                    ->whereBetween('scheduled_time', [$startOfWeek, $endOfWeek])
                    ->delete();

                Log::info("Deleted $expired old intakes for medicine {$medicine->id}");
            }
        }

        return Command::SUCCESS;
    }
}
