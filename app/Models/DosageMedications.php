<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class DosageMedications extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(MedicationSchedule::class);
    }

    public function days()
    {
        return $this->hasMany(MedicationDay::class);
    }

}
