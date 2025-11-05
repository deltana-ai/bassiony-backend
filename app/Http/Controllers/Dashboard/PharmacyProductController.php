<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\PharmacyProductRequest;
use App\Http\Resources\PharmacyProductResource ;
use App\Http\Resources\ProductPharmacyResource;
use App\Interfaces\PharmacyProductRepositoryInterface;
use App\Interfaces\PharmacyRepositoryInterface;
use App\Models\PharmacyProduct;
use Exception;
use Illuminate\Http\Request;

class PharmacyProductController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(PharmacyRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string',
            'category_name' => 'nullable|exists:categories,name',
            'brand_name' => 'nullable|exists:brands,name',
            'branch_name' => 'nullable|exists:branches,name',
            'active' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $pharmacyId = auth()->guard("employees")->user()->pharmacy_id;
            $products = ProductPharmacyResource::collection($this->crudRepository->getPharmacyProducts($pharmacyId));
            
          
            return $products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    // public function index()
    // {
    //     try {

    //         $pharmacy_product = PharmacyProductResource::collection($this->crudRepository->all(
    //             ["pharmacy", "pharmacy_product","pharmacy_product.product:id,name","pharmacy_product.pharmacy:id,name"],
    //             [],
    //             ['*']
    //         ));
    //         return $pharmacy_product->additional(JsonResponse::success());
    //     } catch (Exception $e) {
    //         return JsonResponse::respondError($e->getMessage());
    //     }
    // }

    // public function store(PharmacyProductRequest $request)
    // {
    //         try {
    //             $pharmacy_product = $this->crudRepository->create($request->validated());
                
    //             return new PharmacyProductResource($pharmacy_product);
    //         } catch (Exception $e) {
    //             return JsonResponse::respondError($e->getMessage());
    //         }
    // }

    // public function show(PharmacyProduct $pharmacy_product): ?\Illuminate\Http\JsonResponse
    // {
    //     try {
    //         $pharmacy_product->load(["pharmacy", "pharmacy_product","pharmacy_product.product:id,name","pharmacy_product.pharmacy:id,name"]);

    //         return JsonResponse::respondSuccess('Item Fetched Successfully', new PharmacyProductResource($pharmacy_product));
    //     } catch (Exception $e) {
    //         return JsonResponse::respondError($e->getMessage());
    //     }
    // }


    // public function update(PharmacyProductRequest $request, PharmacyProduct $pharmacy_product)
    // {
    //     $this->crudRepository->update($request->validated(), $pharmacy_product->id);

       
    //     return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    // }


    // public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    // {
    //     try {
    //         $this->crudRepository->deleteRecords('pharmacy_product', $request['items']);
    //         return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
    //     } catch (Exception $e) {
    //         return JsonResponse::respondError($e->getMessage());
    //     }
    // }

 





}
