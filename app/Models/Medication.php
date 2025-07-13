<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medication extends BaseModel
{

      protected $with = [
        'media',
    ];

   protected $fillable = [
        'user_id', 'name', 'note', 'dose_amount',
        'times_per_day', 'start_date', 'end_date',
        'is_expired', 'user_approved_deletion'
    ];

    protected $casts = [
        'is_expired' => 'boolean',
        'user_approved_deletion' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


        public function times()
    {
        return $this->hasMany(MedicationTime::class);
    }

    public function days()
    {
        return $this->hasMany(MedicationDay::class);
    }
    
    public function getDosageAttribute($value)
    {
        return ucfirst($value);
    }
}
