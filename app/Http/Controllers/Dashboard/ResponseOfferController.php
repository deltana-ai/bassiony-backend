<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\ResponseOfferRepositoryInterface;
use App\Helpers\JsonResponse;
use App\Http\Requests\ResponseOfferRequest;
use App\Http\Requests\UpdateOfferStatusRequest;
use App\Http\Resources\ResponseOfferResource;
use Exception;
use App\Models\ResponseOffer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ResponseOfferController extends Controller
{
    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(ResponseOfferRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
         
        // $this->middleware('permission:response-offer-create|manage-pharmacy', ['only' => [ 'store']]);
        // $this->middleware('permission:response-offer-edit|manage-company', ['only' => [ 'updateStatus']]);
        // $this->middleware('permission:response-offer-order|manage-company', ['only' => [ 'getOfferOrders']]);
        // $this->middleware('permission:response-offer-cancel|manage-pharmacy', ['only' => [ 'cancel']]);
        // $this->middleware('permission:response-offer-list|manage-company|manage-pharmacy|mange-site', ['only' => [ 'show','index']]);
        // $this->middleware('permission:response-offe-delete|manage-company', ['only' => ['destroy','restore','forceDelete']]);

    }


    public function index()
    {
        try {
            $this->authorize('viewAny', ResponseOffer::class);

            $company_id = null;

            if (auth()->guard('employees')->check()) {
                $company_id = auth()->guard('employees')->user()->company_id;
            }
            
            $offers = ResponseOfferResource::collection($this->crudRepository->allForCompany($company_id ));
            return $offers->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     *  get warehouse offer orders
     */

    public function getOfferOrders($warehouse_id)
    {
        try{

            $this->authorize('viewOrders',$warehouse_id, ResponseOffer::class);

            $offers = ResponseOfferResource::collection($this->crudRepository->all(
                ["offer","pharmacy","warehouse"],
                ["warehouse_id"=>auth()->guard("employees")->user()->warehouse_id],
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
            $this->authorize('create', ResponseOffer::class);
           // dd(auth()->guard("pharmacists")->check());

            $offer = $this->crudRepository->getBaseOffer($request->company_offer_id); 
            
            if ($request->quantity < $offer->min_quantity || $request->quantity > $offer->total_quantity) {
                return JsonResponse::respondError("هذه الكمية لا تناسب العرض في الوقت الحالي");

            }
            $data = $this->handleData($request,$offer);
           
            $response_offer =  $this->crudRepository->create($data);
           
            return new ResponseOfferResource($response_offer);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            
            $responseOffer = $this->crudRepository->find($id)->load("offer","pharmacy","warehouse");

            $this->authorize('view', $responseOffer);
            return JsonResponse::respondSuccess('Item Fetched Successfully', new ResponseOfferResource($responseOffer));
        
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function updateStatus(UpdateOfferStatusRequest $request,  $id)
    {
        
        $responseOffer = $this->crudRepository->find($id);

        $this->authorize('update', $responseOffer);

        
        try {
             
            $this->crudRepository->updateResponse(  $request->validated(),  $responseOffer);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));

        } catch (\Throwable $th) {
            return JsonResponse::respondError($th->getMessage());
        }

    }


    public function cancel(Request $request ,  $id): ?\Illuminate\Http\JsonResponse
    {
        try {
            $responseOffer = $this->crudRepository->find($id);

            $this->authorize('cancel', $responseOffer);
            $data["status"] = "canceled";
            $this->crudRepository->updateResponse(  $data,  $responseOffer);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_CANCELED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

     public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $responses = ResponseOffer::whereIn('id', $request->items)->get();

            foreach ($responses as $response) {
                $this->authorize('delete', $response); 
            }

            $this->crudRepository->delete( $request['items']);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


     public function restore(Request $request)
    {
        try {
            $responses = ResponseOffer::whereIn('id', $request->items)->get();

            foreach ($responses as $response) {
                $this->authorize('delete', $response); 
            }
            $this->crudRepository->restoreItem(ResponseOffer::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request)
    {
        try {
            $responses = ResponseOffer::whereIn('id', $request->items)->get();

            foreach ($responses as $response) {
                $this->authorize('delete', $response); 
            }
            $this->crudRepository->deleteRecordsFinial(ResponseOffer::class, $request['items']);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    private function handleData(ResponseOfferRequest $request ,$offer)
    {
        $data = $request->validated();
        $data['item_price'] = $offer->product->price ;
        $discount = ($offer->discount/100);
        $value_before_discount = $request->quantity * $offer->product->price ;
        $value_after_discount = $value_before_discount - ($value_before_discount * $discount);
        $data['total_price'] = round($value_after_discount,2) ;
        $data['status'] = 'pending';

        return $data;

    }

    

  







     

}
