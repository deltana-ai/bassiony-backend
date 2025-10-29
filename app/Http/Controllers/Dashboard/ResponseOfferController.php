<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\ResponseOfferRepositoryInterface;
use App\Helpers\JsonResponse;
use App\Http\Requests\ResponseOfferRequest;
use App\Http\Resources\ResponseOfferResource;
use Exception;
use App\Models\ResponseOffer;

class ResponseOfferController extends Controller
{
    protected mixed $crudRepository;

    public function __construct(ResponseOfferRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }


    public function index()
    {
        try {
            $offers = ResponseOfferResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $offers->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(ResponseOfferRequest $request)
    {
            try {
         
                $offer = $this->crudRepository->getBaseOffer($request->company_offer_id); 
                if ($request->quantity < $offer->min_quantity || $request->quantity > $offer->quantity) {
                    return JsonResponse::respondError("هذه الكمية لا تناسب العرض في الوقت الحالي");

                }
                $data = $this->crudRepository->handleData($request,$offer);
                $response_offer =  $this->crudRepository->create($data);
                return new ResponseOfferResource($response_offer);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(ResponseOffer $responseOffer): ?\Illuminate\Http\JsonResponse
    {
        try {
            
            return JsonResponse::respondSuccess('Item Fetched Successfully', new ResponseOfferResource($responseOffer));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function updateStatus(Request $request, ResponseOffer $responseOffer)
    {
        $request->validate([
            'status' => ['required', 'in:approved,rejected,delivered','canceled'],
        ]);
        try {
            
            $this->crudRepository->updateResponse(  $request->status,  $responseOffer);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));

        } catch (\Throwable $th) {
            return JsonResponse::respondError($th->getMessage());
        }

    }


    public function cancel(Request $request , ResponseOffer $responseOffer): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->updateResponse(  "canceled",  $responseOffer);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_CANCELED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

     public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->delete( $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    

  







     

}
