<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ProductCompanyResource;
use App\Interfaces\CompanyRepositoryInterface;
use Illuminate\Http\Request;
use Exception;
use App\Helpers\JsonResponse;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class CompanyProductController extends BaseController
{
    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(CompanyRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
       
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

 
}