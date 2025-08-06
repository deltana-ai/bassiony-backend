<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $guarded = ['id'];


  public function user()
  {
      return $this->belongsTo(User::class);
  }

  public function getFullAddressAttribute()
  {
      return implode(' - ', array_filter([
          $this->name,
          $this->building,
          $this->area,
          $this->city,
          "Tel: {$this->phone}"
      ]));
  }
}
