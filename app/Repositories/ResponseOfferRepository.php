<?php

namespace App\Repositories;

use App\Http\Requests\ResponseOfferRequest;
use App\Interfaces\ResponseOfferRepositoryInterface;
use App\Models\CompanyOffer;
use App\Models\ResponseOffer;
use App\Models\Role;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ResponseOfferRepository extends CrudRepository implements ResponseOfferRepositoryInterface
{
    protected Model $model;
 
    public function __construct(ResponseOffer $model)
    {
        $this->model = $model;
    }

    public function getBaseOffer($offerid)
    {
        return CompanyOffer::findOrFail($offerid);
    }


    

    public function updateResponse( string $status, ResponseOffer $responseOffer)
    {
        $warehouseProduct = $responseOffer->offer->warehouseProduct;

        DB::transaction(function () use ($responseOffer, $warehouseProduct, $status) {
            switch ($status) {
                case 'approved':
                    $warehouseProduct->decrement('stock', $responseOffer->quantity);
                    break;

            }

            $responseOffer->update(['status' => $status]);
        });

    }

    private function handleData(ResponseOfferRequest $request ,$offer)
    {
        $data = $request->validated();
        $data['item_price'] = $offer->product->price ;

        $data['total_price'] = round($request->quantity * $offer->product->price *($offer->discount/100),2) ;
        $data['status'] = 'pending';

        return $data;

    }

   
}

