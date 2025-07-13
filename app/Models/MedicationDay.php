<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationDay extends Model
{
        protected $fillable = ['medication_id', 'day_of_week'];

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
