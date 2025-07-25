<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Offer extends BaseModel
{
    use HasMedia;

    protected $with = [
        'media',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    protected $guarded = ['id'];

    public function pharmacyProduct()
    {
        return $this->belongsTo(PharmacyProduct::class);
    }

    ////////////////////////made by zeinab////////////////////////////////////
    public function isValid(){
      return  now()->between($this->start_date,$this->end_date);
    }
    ///////////////////////////////////////////////////////////
}
