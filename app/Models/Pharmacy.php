<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pharmacy extends BaseModel
{
    use SoftDeletes;

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

}
