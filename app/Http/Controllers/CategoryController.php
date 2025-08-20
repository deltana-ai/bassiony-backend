<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\{Category,Product};
use Exception;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    

   public function index(Request $request)
    {
     try {
        $query = Category::where('active', 1);
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


    public function show(Request $request,$id)
    {
        try {
            $category = Category::find($id);
            $query = Product::where('active', 1)->where('category_id',$category->id);
            $per_page = 10;
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('name', 'LIKE', "%{$search}%");
            }
            if ($request->filled('per_page')) {
                $per_page = $request->get('per_page');
            }
            $products = $query->orderBy('position', 'asc')->paginate($per_page);
            return ProductResource::collection($products);
        } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
        }
    }








}
