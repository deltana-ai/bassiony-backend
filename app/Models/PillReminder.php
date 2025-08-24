<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PillReminder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'dosage',
        'notes',
        'time',
        'repeat',
        'days',
        'user_id',
    ];

    protected $casts = [
        'days' => 'array',
        'repeat' => 'boolean',
        'time' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}