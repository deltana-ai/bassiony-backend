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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WarehouseProductController extends BaseController
{

    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(WarehouseRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }


    public function index()
    {
        try {
            $warehouse_product_products = WarehouseProductResource::collection($this->crudRepository->all(
                [ 'products' ],
                [],
                ['id','name','code']
            ));
            return $warehouse_product_products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(Warehouse $warehouse,WarehouseProductRequest $request)
    {
            try {
                $this->authorize('manage', $warehouse);
                $expiry_date = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                ->format('Y-m-d');
                $exists = $warehouse->products()
                ->where('product_id', $request->product_id)
                ->wherePivot('batch_number', $request->batch_number)
                ->wherePivot('expiry_date', $expiry_date)
                ->exists();

                if ($exists) {
                    return JsonResponse::respondError('This product with the same batch number and expiry date already exists in this warehouse.');
                }
                $warehouse_product = $warehouse->products()->attach($request->product_id,['stock' => $request->stock, 'reserved_stock' => $request->reserved_stock, 'expiry_date' => $expiry_date, 'batch_number' => $request->batch_number]);

                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Warehouse $warehouse, Request $request, int $productId): ?\Illuminate\Http\JsonResponse
    {
        try {
            $warehouse->load(['products' => function ($q) use ($productId, $request) {
                $q->where('products.id', $productId);

                if ($request->filled('batch_number')) {
                    $q->wherePivot('batch_number', $request->batch_number);
                }

                if ($request->filled('expiry_date')) {
                    $expiry_date = Carbon::parse($request->expiry_date)->format('Y-m-d');
                    $q->wherePivot('expiry_date', $expiry_date);
                }

                $q->withPivot(['stock', 'reserved_stock', 'expiry_date', 'batch_number']);
            }]);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new WarehouseProductResource($warehouse));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(WarehouseProductRequest $request, Warehouse $warehouse)
    {
        try {
            $this->authorize('manage', $warehouse);
            $expiry_date = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                ->format('Y-m-d');

            $warehouse->products()->updateExistingPivot(
                $request->product_id,
                [
                    'stock' => $request->stock,
                    'reserved_stock' => $request->reserved_stock,
                    'expiry_date' => $expiry_date,
                    'batch_number' => $request->batch_number,
                ]
            );

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        }

        catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function destroy(Warehouse $warehouse,Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('manage', $warehouse);
            if (!$request->has('items') || !$request->has('batch_number')) {
                return JsonResponse::respondError('Product IDs and batch number are required.');
            }

            $productIds = (array) $request->items;
            $batchNumber = $request->batch_number;


            $warehouse->products()
                ->wherePivot('batch_number', $batchNumber)
                ->whereIn('product_id', $productIds)
                ->detach();
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }






}
