<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\PharmacyRequest;
use App\Http\Resources\Dashboard\PharmacyResource;
use App\Http\Resources\PharmacistResource;
use App\Interfaces\PharmacyRepositoryInterface;
use App\Models\CartItem;
use App\Models\Pharmacist;
use App\Models\Pharmacy;
use Exception;
use Illuminate\Http\Request;

class PharmacyController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(PharmacyRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->middleware('permission:pharmacy-list|manage-site|manage-pharmacy|manage-company', ['only' => ['index','show']]);
        $this->middleware('permission:pharmacy-create|manage-site', ['only' => [ 'store']]);
        $this->middleware('permission:pharmacy-edit|manage-site', ['only' => [ 'update']]);
        $this->middleware('permission:pharmacy-delete|manage-site', ['only' => ['destroy','restore','forceDelete']]);

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
                $employee = $this->crudRepository->createPharmacywithUser($request->validated());
                
                $employee->load("roles");
                
                return JsonResponse::respondSuccess('Item created Successfully', new PharmacistResource($employee));
            
                
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

        $employee = $this->crudRepository->updatePharmacywithUser($request->validated(), $pharmacy->id);
       
        $employee->load("roles");
                
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY), new PharmacistResource($employee));
           

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
