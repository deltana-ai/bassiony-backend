<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{


    
    
   protected $fillable = ['name'];

    public function points()
    {
        return $this->hasMany(Point::class);
    }

    
}
