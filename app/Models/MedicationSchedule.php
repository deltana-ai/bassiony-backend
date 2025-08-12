<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationSchedule extends BaseModel
{
   use  HasFactory;

   protected $guarded = ['id'];

   public function dosageMedications()
    {
        return $this->belongsTo(DosageMedications::class);
    }
}
