<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\ProductRatingRequest;
use App\Http\Resources\ProductRatingResource;
use App\Interfaces\ProductRateRepositoryInterface;
use App\Models\ProductRating;
use Exception;
use Illuminate\Http\Request;

class ProductRatingController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(ProductRateRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $rates = ProductRatingResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $rates->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(ProductRatingRequest $request)
    {
            try {
                $rate = $this->crudRepository->create($request->validated());
                
                return new ProductRatingResource($rate);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(ProductRating $rate): ?\Illuminate\Http\JsonResponse
    {
        try {
            return JsonResponse::respondSuccess('Item Fetched Successfully', new ProductRatingResource($rate));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(ProductRatingRequest $request, ProductRating $rate)
    {
        $this->crudRepository->update($request->validated(), $rate->id);
 
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('product_rates', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    









}
