<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];
}
