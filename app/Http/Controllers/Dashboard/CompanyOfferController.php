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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyOfferController extends Controller
{
    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(CompanyOfferRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }


      public function index()
    {
        try {
            $this->authorize('viewAny', CompanyOffer::class);

            $offers = CompanyOfferResource::collection($this->crudRepository->all(
                [],
                [],
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

                $offer = $this->crudRepository->create($request->validated());

                return new CompanyOfferResource($offer);
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


    public function update(CompanyOfferRequest $request, CompanyOffer $companyOffer)
    {
        try {

            $this->authorize('update', $companyOffer);

            $this->crudRepository->update($request->validated(), $companyOffer->id);

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

  







     

}
