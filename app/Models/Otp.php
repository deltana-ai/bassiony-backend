<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
  protected $fillable = ['email', 'otp', 'expires_at', 'guard'];
  protected $casts = [
        'expires_at' => 'datetime',
  ];
}
