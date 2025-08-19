<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Interfaces\BrandRepositoryInterface;
use App\Models\Brand;
use Exception;
use Illuminate\Http\Request;

class BrandController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(BrandRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index(Request $request)
    {
     try {
        $query = Brand::where('active', 1);
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }
        $brands = $query->orderBy('position', 'asc')->get();
        return BrandResource::collection($brands);
        }
         catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function show(Brand $brand)
    {
        // here we show products belongs to this brand
    }








}
