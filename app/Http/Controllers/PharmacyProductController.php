<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\StorePharmacyProductRequest;
use App\Http\Resources\PharmacyProductResource;
use App\Interfaces\PharmacyProductRepositoryInterface;
use App\Models\PharmacyProduct;
use Exception;
use Illuminate\Http\Request;

class PharmacyProductController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(PharmacyProductRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {
            $pharmacyProducts = PharmacyProductResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $pharmacyProducts->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(PharmacyProduct $pharmacyProduct)
    {
        try {
            $pharmacyProduct = new PharmacyProductResource($pharmacyProduct);
            return $pharmacyProduct->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(StorePharmacyProductRequest $request)
    {
        try {
            $pharmacyProduct = $this->crudRepository->create($request->validated());
            if (request('image') !== null) {
                $this->crudRepository->AddMediaCollection('image', $pharmacyProduct);
            }
            return new PharmacyProductResource($pharmacyProduct);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(StorePharmacyProductRequest $request, PharmacyProduct $pharmacyProduct)
    {
        $this->crudRepository->update($request->validated(), $pharmacyProduct->id);

        $pharmacyProductImage = $pharmacyProduct;
        if (request('image') !== null) {
            $pharmacyProductImage = PharmacyProduct::find($pharmacyProduct->id);
            $image = $this->crudRepository->AddMediaCollection('image', $pharmacyProductImage);
        }
        activity()->performedOn($pharmacyProduct)->withProperties(['attributes' => $pharmacyProductImage])->log('update');
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $count = $this->crudRepository->deleteRecords('sliders', $request['items']);
            return $count > 1
                ? JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED_MULTI_RESOURCE))
                : ($count == 222 ? JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED))
                    : JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY)));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(PharmacyProduct::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(PharmacyProduct::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function indexPublic()
    {
        try {

            $pharmacyProducts = PharmacyProduct::where('active', 1)
                ->orderBy('position', 'asc')
                ->get();


            return PharmacyProductResource::collection($pharmacyProducts);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}
