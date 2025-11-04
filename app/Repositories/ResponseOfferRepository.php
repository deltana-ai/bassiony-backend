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

   


    public function allForCompany($companyId = null)
    {
        $options = [];

        if (auth()->guard('pharmacists')->check()) {
            $options["pharmacy_id"] = auth()->guard('pharmacists')->user()->pharmacy_id;

            return $this->all(
                ['offer'],
                $options,
                ['*']
            );
        }

        if (auth()->guard('admins')->check()) {
            return $this->all(['offer']);
        }

        if ($companyId) {
            return $this->all(
                ['offer'],
                $options,
                ['*'],
                function ($query) use ($companyId) {
                    return $query->whereHas('offer', function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    });
                }
            );
        }

        return $this->all(['offer'], $options, ['*']);
    }

    



    public function getBaseOffer($offerid)
    {
        return CompanyOffer::findOrFail($offerid);
    }


    

    public function updateResponse( string $status, ResponseOffer $responseOffer)
    {
        $warehouseProduct = $responseOffer->offer->warehouseProduct;
        $offer = $responseOffer->offer;
        DB::transaction(function () use ($responseOffer, $warehouseProduct, $status,$offer) {
            switch ($status) {
                case 'approved':
                    // $warehouseProduct->decrement('stock', $responseOffer->quantity);
                    $offer->decrement('total_quantity', $responseOffer->quantity);
                    break;

            }

            $responseOffer->update(['status' => $status]);
        });

    }

   
   
}

