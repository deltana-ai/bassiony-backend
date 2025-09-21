<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\WarehouseProductRequest;
use App\Http\Resources\WarehouseProductResource ;
use App\Interfaces\WarehouseProductRepositoryInterface;
use App\Models\WarehouseProduct;
use Exception;
use Illuminate\Http\Request;

class WarehouseProductController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(WarehouseProductRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $warehouse_products = WarehouseProductResource::collection($this->crudRepository->all(
                ["company", "company_product","company_product.product:id,name","company_product.company:id,name"],
                [],
                ['*']
            ));
            return $warehouse_products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(WarehouseProductRequest $request)
    {
            try {
                $branch = $this->crudRepository->create($request->validated());
                
                return new WarehouseProductResource($branch);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(WarehouseProduct $branch): ?\Illuminate\Http\JsonResponse
    {
        try {
            $branch->load(["company", "company_product","company_product.product:id,name","company_product.company:id,name"]);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new WarehouseProductResource($branch));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(WarehouseProductRequest $request, WarehouseProduct $branch)
    {
        $this->crudRepository->update($request->validated(), $branch->id);

       
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('warehouse_products', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }





   





}
