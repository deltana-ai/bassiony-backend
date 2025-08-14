<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasMedia;

class Brand extends BaseModel
{
    use HasFactory,HasMedia, SoftDeletes;
    protected $with = [
        'media',
    ];
    protected $guarded = ['id'];
    protected $casts = [
        'active' => 'boolean',
        'show_home'=>'boolean'
    ];

}
