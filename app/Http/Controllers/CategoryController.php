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

    public function index()
    {
      try {

          $categories = Category::where('active', 1)
              ->orderBy('position', 'asc')->take(20)
              ->get();


          return CategoryResource::collection($categories);
      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }
    }


    public function show(Category $category): ?\Illuminate\Http\JsonResponse
    {
        // here we show products belongs to this Category
    }








}
