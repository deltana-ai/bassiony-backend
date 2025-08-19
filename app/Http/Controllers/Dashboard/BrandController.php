<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\Dashboard\BrandResource;
use App\Interfaces\BrandRepositoryInterface;
use App\Models\Brand;
use Exception;
use Illuminate\Http\Request;

class BrandController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(BrandRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $brands = BrandResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $brands->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(BrandRequest $request)
    {
            try {
                $brand = $this->crudRepository->create($request->validated());
                if (request('image') !== null) {
                    $this->crudRepository->AddMediaCollection('image', $brand);
                }
                return new BrandResource($brand);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Brand $brand): ?\Illuminate\Http\JsonResponse
    {
        try {
            return JsonResponse::respondSuccess('Item Fetched Successfully', new BrandResource($brand));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(BrandRequest $request, Brand $brand)
    {
        $this->crudRepository->update($request->validated(), $brand->id);

        $brandImage = $brand;
        if (request('image') !== null) {
            $brandImage = Brand::find($brand->id);
            $image = $this->crudRepository->AddMediaCollection('image', $brandImage);
        }
        activity()->performedOn($brand)->withProperties(['attributes' => $brandImage])->log('update');
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('brands', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(Brand::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(Brand::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }





}
