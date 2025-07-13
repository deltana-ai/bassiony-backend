<?php 


namespace App\Repositories;
use App\Models\Medication;
use App\Models\MedicationDay;
use App\Models\MedicationTime;
use Illuminate\Database\Eloquent\Collection;



class MedicationRepository
{ 
    
    
    public function create(array $data)
    {
        return Medication::create($data);
    }

      public function getTodayMedications($userId, $today)
    {
        return Medication::with('times')
            ->where('user_id', $userId)
            ->where('is_expired', false)
            ->whereHas('days', function ($query) use ($today) {
                $query->where('day_of_week', $today);
            })
            ->get();
    }

    
    
 public function findById($id, $userId)
    {
        return Medication::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    
}