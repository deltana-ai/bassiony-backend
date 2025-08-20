<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ProductResource;
use App\Models\{Brand,Product};
use Exception;
use Illuminate\Http\Request;

class BrandController extends BaseController
{
   

    public function index(Request $request)
    {
     try {
        $query = Brand::where('active', 1);
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


    public function show(Request $request,$id)
    {
        try {
            $brand = Brand::find($id);
            $query = Product::where('active', 1)->where('brand_id',$brand->id)->whereHas('pharmacies');
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








}
