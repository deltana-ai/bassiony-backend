<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\PharmacyRequest;
use App\Http\Resources\Dashboard\PharmacyResource;
use App\Interfaces\PharmacyRepositoryInterface;
use App\Models\CartItem;
use App\Models\Pharmacy;
use Exception;
use Illuminate\Http\Request;

class PharmacyController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(PharmacyRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $pharmacies = PharmacyResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $pharmacies->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(PharmacyRequest $request)
    {
            try {
                $pharmacy = $this->crudRepository->createPharmacywithUser($request->validated());

                return new PharmacyResource($pharmacy);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Pharmacy $pharmacy): ?\Illuminate\Http\JsonResponse
    {
        try {
            return JsonResponse::respondSuccess('Item Fetched Successfully', new PharmacyResource($pharmacy));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(PharmacyRequest $request, Pharmacy $pharmacy)
    {
        $this->crudRepository->updatePharmacywithUser($request->validated(), $pharmacy->id);


        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deletePharmacywithUsers( $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restorePharmacywithUsers( $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(Pharmacy::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }






   


}
