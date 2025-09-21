<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\CompanyProductRequest;
use App\Http\Resources\CompanyProductResource ;
use App\Interfaces\CompanyProductRepositoryInterface;
use App\Models\CompanyProduct;
use Exception;
use Illuminate\Http\Request;

class CompanyProductController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(CompanyProductRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $company_products = CompanyProductResource::collection($this->crudRepository->all(
                ["company:id,name", "product:id,name"],
                [],
                ['*']
            ));
            return $company_products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(CompanyProductRequest $request)
    {
            try {
                $company_product = $this->crudRepository->create($request->validated());
                
                return new CompanyProductResource($company_product);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(CompanyProduct $company_product): ?\Illuminate\Http\JsonResponse
    {
        try {
            $company_product->load(['company:id,name', 'product:id,name']);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new CompanyProductResource($company_product));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(CompanyProductRequest $request, CompanyProduct $company_product)
    {
        $this->crudRepository->update($request->validated(), $company_product->id);

       
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('company_products', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

   







}
