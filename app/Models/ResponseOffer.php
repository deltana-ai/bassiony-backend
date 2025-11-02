<?php

namespace App\Models;

use App\Policies\ResponseOfferPolicy;
use Illuminate\Database\Eloquent\Model;

class ResponseOffer extends BaseModel
{
    protected $table = 'response_offer'; 
    protected $guarded = ['id'];

    
    public function offer()
    {
        return $this->belongsTo(CompanyOffer::class, 'company_offer_id');
    }

    
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public static function policy()
    {
        return ResponseOfferPolicy::class;
    }
}
