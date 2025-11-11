<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\CompanyOfferRepositoryInterface;
use App\Helpers\JsonResponse;
use App\Http\Requests\CompanyOfferRequest;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyOfferResource;
use Exception;
use App\Models\Company;
use App\Models\CompanyOffer;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyOfferController extends Controller
{
    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(CompanyOfferRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->middleware('permission:company-offer-create|manage-company', ['only' => [ 'store']]);
        $this->middleware('permission:company-offer-edit|manage-company', ['only' => [ 'update']]);
        $this->middleware('permission:company-offer-delete|manage-company', ['only' => ['destroy','restore','forceDelete']]);
    }


    public function index()
    {
        try {
            $this->authorize('viewAny', CompanyOffer::class);
            $options = [];
            if (auth()->guard("employees")->check()) {
                $options["company_id"] = auth()->guard("employees")->user()->company_id ;
            }
            $offers = CompanyOfferResource::collection($this->crudRepository->all(
                [],
                $options,
                ['*']
            ));
            return $offers->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(CompanyOfferRequest $request)
    {
            try {
                $this->authorize('create', CompanyOffer::class);
                $data = $this->handleData( $request);
                $offer = $this->crudRepository->create($data);

               // return new CompanyOfferResource($offer);
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));

            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(CompanyOffer $companyOffer): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('view', $companyOffer);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new CompanyOfferResource($companyOffer));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(CompanyOfferRequest $request, $id)
    {
        try {
            $companyOffer = $this->crudRepository->find($id);
            $this->authorize('update',$companyOffer);
            $data = $this->handleData( $request);
            $this->crudRepository->update($data, $companyOffer->id);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));

        } catch (\Throwable $th) {
            return JsonResponse::respondError($th->getMessage());
        }

    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $responses = CompanyOffer::whereIn('id', $request->items)->get();

            foreach ($responses as $response) {
                $this->authorize('delete', $response); 
            }

            $this->crudRepository->delete( $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $responses = CompanyOffer::whereIn('id', $request->items)->get();

            foreach ($responses as $response) {
                $this->authorize('delete', $response); 
            }
            $this->crudRepository->restoreItem(CompanyOffer::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $responses = CompanyOffer::whereIn('id', $request->items)->get();

            foreach ($responses as $response) {
                $this->authorize('delete', $response); 
            }
            $this->crudRepository->deleteRecordsFinial(CompanyOffer::class, $request['items']);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    private function handleData(CompanyOfferRequest $request)
    {
            $data = $request->validated();
            if (!empty($data['start_date'])) {
                $start_date = Carbon::createFromFormat('d-m-Y', $request->start_date)
                ->format('Y-m-d');
                $data['start_date'] = $start_date;
            }
            if (!empty($data['end_date'])) {
                $end_date = Carbon::createFromFormat('d-m-Y', $request->end_date)
                ->format('Y-m-d');
                $data['end_date'] = $end_date;
            }
            
            
            $data['company_id'] = auth()->guard("employees")->user()->company_id;
            return $data ;
    }
  







     

}
