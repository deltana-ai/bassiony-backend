<?php

namespace App\Models;

use App\Traits\HasMedia;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

use Laravel\Sanctum\HasApiTokens;

use Laravel\Sanctum\PersonalAccessToken;

use Illuminate\Contracts\Auth\MustVerifyEmail;

class CompanyManager extends Authenticatable implements MustVerifyEmail
{
  use HasFactory, Notifiable, HasApiTokens, HasMedia, SoftDeletes;
  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $guarded = ['id'];


  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
      'password',
      'remember_token',
  ];
  protected $casts = [
      'email_verified_at' => 'datetime',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
}
