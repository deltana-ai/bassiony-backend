<?php

// app/Models/MedicineIntake.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineIntake extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'schedule_id',
        'scheduled_time',
        'actual_time',
        'taken'
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function schedule()
    {
        return $this->belongsTo(MedicineSchedule::class);
    }
}