<?php

namespace App\Services;

use App\Models\Medication;
use App\Models\MedicationTime;
use App\Models\MedicationDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicationRepository;

class MedicationService
{
    protected $repo;

    public function __construct(MedicationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function createMedication(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $medication = $this->repo->create([
                'user_id' => $userId,
                'name' => $data['name'],
                'note' => $data['note'] ?? null,
                'dose_amount' => $data['dose_amount'],
                'times_per_day' => $data['times_per_day'],
                'start_date' => now(),
                'end_date' => now()->addDays(30),
            ]);

            foreach ($data['times'] as $time) {
                MedicationTime::create(['medication_id' => $medication->id, 'time' => $time]);
            }

            foreach ($data['days'] as $day) {
                MedicationDay::create(['medication_id' => $medication->id, 'day_of_week' => $day]);
            }

            Cache::forget("user_{$userId}_medications_today");

            return $medication;
        });
    }

    public function getTodayMedications($userId)
    {
        $today = now()->format('l');

        return Cache::remember("user_{$userId}_medications_today", 600, function () use ($userId, $today) {
            return $this->repo->getTodayMedications($userId, $today);
        });
    }

  public function handleExpirationDecision($id, $userId, $approve): Medication
{
    $med = $this->repo->findById($id, $userId);
    $med->user_approved_deletion = $approve;
    $med->save();
    return $med;
}


public function deleteMedication($id, $userId): bool
{
    $med = $this->repo->findById($id, $userId);
    return $med->delete();
}

public function searchMedications($userId, $search = null, $day = null)
{
    $query = \App\Models\Medication::with('times')
        ->where('user_id', $userId)
        ->where('is_expired', false);

    if ($search) {
        $query->where('name', 'LIKE', "%{$search}%");
    }

    if ($day) {
        $query->whereHas('days', fn($q) => $q->where('day_of_week', $day));
    }

    return $query->get();
}




}
