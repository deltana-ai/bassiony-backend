<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ProductCompanyResource;
use App\Interfaces\CompanyRepositoryInterface;
use Illuminate\Http\Request;
use Exception;
use App\Helpers\JsonResponse;
use App\Http\Requests\CompanyPriceRequest;
use App\Http\Resources\CompanyPriceResource;
use App\Http\Resources\CompanyProductResource;
use App\Http\Resources\ProductResource;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\CompanyPrice;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class CompanyProductController extends BaseController
{
    use AuthorizesRequests;
    protected mixed $crudRepository;
    protected mixed $crudProductRepository;
    public function __construct(CompanyRepositoryInterface $pattern ,ProductRepositoryInterface $repo)
    {
        $this->crudRepository = $pattern;
        $this->crudProductRepository = $repo;

        $this->middleware('permission:company-product-list|manage-company', ['only' => [ 'index']]);

       
    }
    /**
     * Get all products for company with total stock
     * GET /api/companies/{company}/products
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string',
            'category_name' => 'nullable|exists:categories,name',
            'brand_name' => 'nullable|exists:brands,name',
            'warehouse_name' => 'nullable|exists:warehouses,name',
            'active' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $companyId = auth()->guard("employees")->user()->company_id;
            $products = ProductCompanyResource::collection($this->crudRepository->getCompanyProducts($companyId));
            
          
            return $products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function productsAll()
    {
        try {
            $companyId = auth()->guard("employees")->user()->company_id;
            $discount_percent = request("discount_percent") ?? null;
            $products = CompanyProductResource::collection($this->crudProductRepository->anotherAll(
                ['companyPrice' => function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
					
                }],
                [],
                ['*'],
                function ($query) use ($companyId, $discount_percent) {
       
                    if ($discount_percent != null) {
                        $query->whereHas('companyPrice', function($q) use ($companyId, $discount_percent) {
                            $q->where('company_id', $companyId)
                            ->where('discount_percent', $discount_percent);
                            
                        });
                        
                    }
                    return $query;
                }
            ));
			
			
            return $products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * store new company price.
     */
    public function storePrice(CompanyPriceRequest $request)
    {
       
        try {
            $data = $request->validated();
            $data["company_id"] =  auth("employees")->user()->company_id;
            $company_price = CompanyPrice::create($data);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }

    }


    
    /**
     * Update the specified company price.
     */
    public function updatePrice(CompanyPriceRequest $request, CompanyPrice $companyPrice)
    {
        try {

            $companyId = Auth::guard('employees')->user()->company_id;

            if ($companyPrice->company_id != $companyId) {

                return JsonResponse::respondError("Unauthorized",403);

            }

            $companyPrice->update([
                'product_id' => $request->product_id,
                'discount_percent' => $request->discount_percent,
            ]);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }

    }

    /**
     * Show the product with the company's price.
     */
    public function showProductPrice(Product $product)
    {
        try {
            $companyId = Auth::guard('employees')->user()->company_id;

            $companyPrice = CompanyPrice::where('company_id', $companyId)
                ->where('product_id', $product->id)
                ->first();

            if (!$companyPrice) {
                return JsonResponse::respondError("Price not found",404);
            }

            return JsonResponse::respondSuccess('Item Fetched Successfully', new CompanyPriceResource($companyPrice));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }





 
}