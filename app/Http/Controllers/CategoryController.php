<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\{Category,Product};
use Exception;
use Illuminate\Http\Request;
use App\Repositories\ModelFilterRepository;
class CategoryController extends BaseController
{
   protected $repo;

   public function __construct(ModelFilterRepository $repo)
    {
        $this->repo = $repo;
    }
   public function index(Request $request)
    {
     try {
        $query = Category::where('active', 1);
        
        $categories = $this->repo->applyFilters($query, $request );

        return CategoryResource::collection($categories);
        }
         catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function show(Request $request,$id)
    {
        try {
            $category = Category::find($id);
            $query = Product::where('active', 1)->where('category_id',$category->id)->whereHas('pharmacies');
            $products = $this->repo->applyFilters($query, $request );

            return ProductResource::collection($products);
        } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
        }
    }








}
