<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends BaseModel
{
   use  HasFactory;

   protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pharmacist()
    {
        return $this->belongsTo(Pharmacist::class, 'pharmacist_id');
    }

}
