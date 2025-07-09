<?php
// app/Models/Medicine.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'notes',
        'dosage_per_intake',
        'image_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(MedicineSchedule::class);
    }

    public function intakes()
    {
        return $this->hasMany(MedicineIntake::class);
    }
}