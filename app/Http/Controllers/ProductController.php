<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\FileImportRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Imports\ProductsImport;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends BaseController

{
    protected mixed $crudRepository;

    public function __construct(ProductRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {
            $products = ProductResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Product $product)
    {
        try {
            $product = new ProductResource($product);
            return $product->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(ProductRequest $request)
    {
        try {
            $product = $this->crudRepository->create($request->validated());
            if (request('image') !== null) {
                $this->crudRepository->AddMediaCollection('image', $product);
            }
            return new ProductResource($product);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(ProductRequest $request,Product $product)
    {
        $this->crudRepository->update($request->validated(), $product->id);

        $productImage = $product;
        if (request('image') !== null) {
            $productImage = Product::find($product->id);
            $image = $this->crudRepository->AddMediaCollection('image', $productImage);
        }
        activity()->performedOn($product)->withProperties(['attributes' => $productImage])->log('update');
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $count = $this->crudRepository->deleteRecords('products', $request['items']);
            return $count > 1
                ? JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED_MULTI_RESOURCE))
                : ($count == 222 ? JsonResponse::respondError(trans(JsonResponse::MSG_CANNOT_DELETED))
                    : JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY)));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(Product::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(Product::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function indexPublic(Request $request)
    {
        try {
        $query = Product::where('active', 1);
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }
        $products = $query->orderBy('position', 'asc')->get();
        return ProductResource::collection($products);
    } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 public function import(FileImportRequest $request)
    {
        try {
            $import = Excel::queueImport(new ProductsImport, $request->file('file'));

            return JsonResponse::respondSuccess( 'جاري استيراد المنتجات في الخلفية، سيتم الإشعار عند الانتهاء ');
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
        
    }
 

}
