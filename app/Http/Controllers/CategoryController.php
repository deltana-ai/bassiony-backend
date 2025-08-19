<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(CategoryRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

   public function index(Request $request)
    {
     try {
        $query = Category::where('active', 1);
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }
        $categories = $query->orderBy('position', 'asc')->get();
        return CategoryResource::collection($categories);
        }
         catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function show(Category $category)
    {
        // here we show products belongs to this Category
    }








}
