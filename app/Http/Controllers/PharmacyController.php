<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\{PharmacyResource,PharmacyDetailsResource,ProductResource,CategoryResource,BrandResource};
use App\Models\{Pharmacy,Product,Category,Brand};
use Exception;

class PharmacyController extends BaseController
{

    public function index(Request $request){
        try {
            $query = Pharmacy::query()->select('id','name')->withAvg('ratings','rating')->withCount('ratings');
            $per_page = 10;
            if ($request->filled('per_page')) {
                $per_page = $request->get('per_page');
            }
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('name', 'LIKE', "%{$search}%");
            }
            $pharmacies = $query->paginate($per_page);
            return PharmacyResource::collection($pharmacies);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function show($id){
        try {
            if (is_null($id)) {
                return JsonResponse::respondError('Pharmacy id is required');
            }
            $pharmacy = Pharmacy::withAvg('ratings','rating')->withCount('ratings')->find($id);
            $pharmacy = new PharmacyDetailsResource($pharmacy);
            return $pharmacy->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function getPharmacyOffers(Request $request,$id){
        try {
            if (is_null($id)) {
                return JsonResponse::respondError('Pharmacy id is required');
            }
            $query = Product::where('active', 1)->whereHas('offers')->whereHas('pharmacies',function($q) use ($id){
                $q->where('pharmacies.id','=',$id);
            });
            $per_page = 10;
            if ($request->filled('per_page')) {
                $per_page = $request->get('per_page');
            }
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('name', 'LIKE', "%{$search}%");
            }
            $products = $query->orderBy('position', 'asc')->paginate($per_page);
            return ProductResource::collection($products);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function getPharmacyProducts(Request $request,$id){
        try {
            if (is_null($id)) {
                return JsonResponse::respondError('Pharmacy id is required');
            }
            $query = Product::where('active', 1)->whereHas('pharmacies',function($q) use ($id){
                $q->where('pharmacies.id','=',$id);
            });
            $per_page = 10;
            if ($request->filled('per_page')) {
                $per_page = $request->get('per_page');
            }
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('name', 'LIKE', "%{$search}%");
            }
            $products = $query->orderBy('position', 'asc')->paginate($per_page);
            return ProductResource::collection($products);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function getPharmacyCategories(Request $request,$id)
    {
     try {
        if (is_null($id)) {
            return JsonResponse::respondError('Pharmacy id is required');
        }
        $query = Category::query()->where('active', 1)
            ->select('categories.*')
            ->whereHas('products.pharmacies', function ($q) use ($id) {
                $q->where('pharmacies.id', $id);
            });
        $per_page = 10;
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }
        if ($request->filled('per_page')) {
            $per_page = $request->get('per_page');
        }
        $categories = $query->orderBy('position', 'asc')->paginate($per_page);
        return CategoryResource::collection($categories);
        }
         catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function getPharmacyBrands(Request $request,$id)
    {
     try {
        if (is_null($id)) {
            return JsonResponse::respondError('Pharmacy id is required');
        }
        $query = Brand::query()->where('active', 1)
            ->select('brands.*')
            ->whereHas('products.pharmacies', function ($q) use ($id) {
                $q->where('pharmacies.id', $id);
            });
        $per_page = 10;
        if ($request->filled('per_page')) {
            $per_page = $request->get('per_page');
        }
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }
        $brands = $query->orderBy('position', 'asc')->paginate($per_page);;
        return BrandResource::collection($brands);
        }
         catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


}
