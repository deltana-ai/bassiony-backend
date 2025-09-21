<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasMedia;
class Pharmacy extends BaseModel
{
     use HasMedia, SoftDeletes;
    protected $with = [
        'media',
    ];
   protected $guarded = ['id'];

   public function ratings()
   {
        return $this->hasMany(PharmacyRating::class);
   }

    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('price', 'stock')
                    ->withTimestamps();
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function pharmacists()
    {
        return $this->hasMany(Pharmacist::class);
    }

}
