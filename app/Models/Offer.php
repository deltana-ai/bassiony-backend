<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
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


    ///////////////////////////////////////////////////////////
}
