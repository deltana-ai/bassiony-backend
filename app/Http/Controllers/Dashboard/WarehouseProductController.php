<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\WarehouseProductRequest;
use App\Http\Resources\WarehouseProductResource ;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class WarehouseProductController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(WarehouseRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }


    public function index()
    {
        try {

            $warehouse_product_products = WarehouseProductResource::collection($this->crudRepository->all(
                [ 'products','company:id,name'],
                [],
                ['*']
            ));
            return $warehouse_product_products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(Warehouse $warehouse,WarehouseProductRequest $request)
    {
            try {
                $expiry_date = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                ->format('Y-m-d');
                $warehouse_product = $warehouse->products()->attach($request->product_id,['warehouse_price' => $request->warehouse_price, 'stock' => $request->stock, 'reserved_stock' => $request->reserved_stock, 'expiry_date' => $expiry_date, 'batch_number' => $request->batch_number]);
                
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Warehouse $warehouse, int $productId): ?\Illuminate\Http\JsonResponse
    {
        try {
             $warehouse->load([
            'products' => function ($q) use ($productId) {
                $q->where('products.id', $productId);
            },
            'company:id,name'
        ]);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new WarehouseProductResource($warehouse));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(WarehouseProductRequest $request, Warehouse $warehouse)
    {
        try {
            $expiry_date = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                ->format('Y-m-d');
            $warehouse->products()->syncWithoutDetaching($request->product_id,['warehouse_price' => $request->warehouse_price, 'stock' => $request->stock, 'reserved_stock' => $request->reserved_stock, 'expiry_date' => $expiry_date, 'batch_number' => $request->batch_number]);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        }
        
        catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function destroy(Warehouse $warehouse,Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $warehouse->products()->detach($request->items);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }






}
