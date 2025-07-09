<?php

namespace App\Models;

use App\Traits\HasMedia;


class Category extends BaseModel
{
    use HasMedia;
    protected $with = [
        'media',
    ];

    protected $casts = [
        'active' => 'boolean',
        'show_home' => 'boolean',
    ];
    protected $guarded = ['id'];
}
