<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Controllers\BaseController;
use App\Http\Resources\{PharmacyResource, PharmacyDetailsResource, ProductResource, CategoryResource, BrandResource};
use App\Models\{Pharmacy, Product, Category, Brand};
use App\Repositories\EnterpriseRepository;
use Illuminate\Http\Request;
use Exception;

class PharmacyController extends BaseController
{
    protected $repo;

    public function __construct(EnterpriseRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        try {
            $query = Pharmacy::query()
                ->select('id','name')
                ->withAvg('ratings','rating')
                ->withCount('ratings');

            $pharmacies = $this->repo->applyFilters($query, $request);

            return PharmacyResource::collection($pharmacies);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            if (is_null($id)) {
                return JsonResponse::respondError('Pharmacy id is required');
            }

            $pharmacy = Pharmacy::withAvg('ratings','rating')
                ->withCount('ratings')
                ->find($id);

            return (new PharmacyDetailsResource($pharmacy))
                ->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function getPharmacyProducts(Request $request, $id)
    {
        try {
            $query = Product::query();

            $products = $this->repo->applyFilters($query, $request, $id, 'pharmacies');

            return ProductResource::collection($products);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function getPharmacyOffers(Request $request, $id)
    {
        try {
            $query = Product::where('active', 1)
                ->whereHas('offers');

            $products = $this->repo->applyFilters($query, $request, $id , 'pharmacies');

            return ProductResource::collection($products);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function getPharmacyCategories(Request $request, $id)
    {
        try {
            $query = Category::query()->select('categories.*');

            $categories = $this->repo->applyFilters($query, $request, $id , 'products.pharmacies');

            return CategoryResource::collection($categories);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function getPharmacyBrands(Request $request, $id)
    {
        try {
            $query = Brand::query()->select('brands.*');

            $brands = $this->repo->applyFilters($query, $request, $id , 'products.pharmacies');

            return BrandResource::collection($brands);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}