<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyRating extends BaseModel
{
   use  HasFactory;

   protected $guarded = ['id'];

   public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
  }

}
