<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pharmacist extends BaseModel
{
    use SoftDeletes;
    
    protected $guarded = ['id'];

    public function points()
    {
        return $this->hasMany(Point::class, 'pharmacist_id');
    }

}
