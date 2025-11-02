<?php

namespace App\Interfaces;

use App\Http\Requests\ResponseOfferRequest;
use App\Interfaces\Interfaces\ICrudRepository;
use App\Models\ResponseOffer;

interface ResponseOfferRepositoryInterface extends ICrudRepository
{
   public function getBaseOffer($offerid);

   
   public function updateResponse( string $status, ResponseOffer $responseOffer);


}
