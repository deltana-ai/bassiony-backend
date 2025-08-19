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

    public function index()
    {
      try {

          $brands = Brand::where('active', 1)
              ->orderBy('position', 'asc')->take(20)
              ->get();


          return BrandResource::collection($brands);
      } catch (Exception $e) {
          return JsonResponse::respondError($e->getMessage());
      }
    }


    public function show(Brand $brand)
    {
        // here we show products belongs to this brand
    }








}
