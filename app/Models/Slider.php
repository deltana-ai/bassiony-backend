<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slider extends BaseModel
{
    use HasMedia , SoftDeletes;

    protected $with = [
        'media',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean'
    ];
}
