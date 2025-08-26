<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Pharmacist extends BaseModel
{
    use HasApiTokens, HasMedia, SoftDeletes;

    protected $with = [
            'media',
        ];

    protected $guarded = ['id'];

    public function points()
    {
        return $this->hasMany(Point::class, 'pharmacist_id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }


}

