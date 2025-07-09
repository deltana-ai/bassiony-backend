<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'times',
        'days',
    ];

    protected $casts = [
        'times' => 'array',
        'days' => 'array',
    ];

    /**
     * أسماء الأيام مع رقمها المقابل
     */
    protected static $daysMap = [
        1 => 'sunday',
        2 => 'monday',
        3 => 'tuesday',
        4 => 'wednesday',
        5 => 'thursday',
        6 => 'friday',
        7 => 'saturday',
    ];

    /**
     * ✅ إرجاع الأيام كنصوص للمستخدم
     */
    public function getDayNamesAttribute()
    {
        return array_map(function ($day) {
            return self::$daysMap[$day] ?? $day;
        }, $this->days ?? []);
    }

    /**
     * ✅ تحويل الأيام النصية لأرقام عند الحفظ (لو أرسلها API)
     */
    public function setDaysAttribute($value)
    {
        // لو المستخدم بعت أيام نصية زي ["monday", "friday"]
        if (is_array($value) && is_string($value[0])) {
            $reverseMap = array_flip(self::$daysMap);

            $this->attributes['days'] = json_encode(array_map(function ($day) use ($reverseMap) {
                return $reverseMap[strtolower($day)] ?? $day;
            }, $value));
        } else {
            // لو هي أرقام عادي
            $this->attributes['days'] = json_encode($value);
        }
    }

    /**
     * علاقة بـ Medicine
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * علاقة بـ الجرعات (intakes)
     */
    public function intakes()
    {
        return $this->hasMany(MedicineIntake::class);
    }
}
