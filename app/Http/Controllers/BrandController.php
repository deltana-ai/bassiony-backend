<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ProductResource;
use App\Models\{Brand,Product};
use Exception;
use Illuminate\Http\Request;
use App\Repositories\ModelFilterRepository;

class BrandController extends BaseController
{
   
    protected $repo;

    public function __construct(ModelFilterRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
     try {
        $query = Brand::where('active', 1);
        $brands = $this->repo->applyFilters($query, $request);
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
            $products = $this->repo->applyFilters($query, $request );
            return ProductResource::collection($products);
        } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
        }
    }








}
