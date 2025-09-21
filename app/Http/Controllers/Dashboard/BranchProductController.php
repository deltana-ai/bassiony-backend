<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\BranchProductRequest;
use App\Http\Resources\BranchProductResource ;
use App\Interfaces\BranchProductRepositoryInterface;
use App\Models\BranchProduct;
use Exception;
use Illuminate\Http\Request;

class BranchProductController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(BranchProductRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $branch_product_products = BranchProductResource::collection($this->crudRepository->all(
                ['branch_product:id,name', 'pharmacy_product','branch_product.pharmacy:id,name','pharmacy_product.product:id,name'],
                [],
                ['*']
            ));
            return $branch_product_products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(BranchProductRequest $request)
    {
            try {
                $branch_product = $this->crudRepository->create($request->validated());
                
                return new BranchProductResource($branch_product);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(BranchProduct $branch_product): ?\Illuminate\Http\JsonResponse
    {
        try {
            $branch_product->load(['branch_product:id,name', 'pharmacy_product','branch_product.pharmacy:id,name','pharmacy_product.product:id,name']);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new BranchProductResource($branch_product));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(BranchProductRequest $request, BranchProduct $branch_product)
    {
        $this->crudRepository->update($request->validated(), $branch_product->id);

       
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('branch_products', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }






}
