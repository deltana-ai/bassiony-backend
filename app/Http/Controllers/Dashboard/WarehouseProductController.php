<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\DeleteBatchRequest;
use App\Http\Requests\FileImportRequest;
use App\Http\Requests\{ReservedStockRequest,UpdateBatchStockRequest };
use App\Http\Requests\WarehouseProductRequest;
use App\Http\Resources\BatchResource;
use App\Http\Resources\WarehouseProduct2Resource;
use App\Http\Resources\WarehouseProductResource ;
use App\Imports\WarehouseProductBatchImport;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\WarehouseProductBatch;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseProductController extends BaseController
{

    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(WarehouseRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }


    public function index($id)
    {
        try {
             
            $warehouse_product_products = WarehouseProduct2Resource::collection($this->crudRepository->getWarehouseProducts( $id));
            return $warehouse_product_products->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    
    public function addBatch(Warehouse $warehouse,WarehouseProductRequest $request){
        try {
            $this->authorize('manage', $warehouse);
            $data = $this->handleData( $request, $warehouse->id);
            $check_data = $data;
            unset($check_data["stock"]);
            DB::transaction(function () use ($check_data, $data, $warehouse, $request) {
                $batch = WarehouseProductBatch::where($check_data)->first();

                if ($batch) {
                    $batch->increment('stock', $data['stock']);
                } else {
                    if (!$warehouse->products()->where('product_id', $request->product_id)->exists()) {
                        $warehouse->products()->attach($request->product_id, [
                            'reserved_stock' => 0,
                        ]);
                    }

                    WarehouseProductBatch::create($data);
                }
            });
                        
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////

    public function addReservedStock(Warehouse $warehouse ,ReservedStockRequest $request)
    {
       try {
            $this->authorize('manage', $warehouse);
            if($warehouse->products()->where("product_id" ,$request->product_id)->exists())
            {
                $warehouse->products()->updateExistingPivot($request->product_id, [
                    'reserved_stock' => $request->reserved_stock,
                ]);
            }
            else {
                $warehouse->products()->attach($request->product_id, [
                    'reserved_stock' => $request->reserved_stock,
                ]);
            
            }
            
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////

    public function show(Warehouse $warehouse, Request $request, int $productId)
    {
        try {
            
            $batches = $this->crudRepository->getProductBatches( $productId, $warehouse->id);
            return JsonResponse::respondSuccess('Item Fetched Successfully', BatchResource::collection($batches));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////

    public function updateBatchStock(Warehouse $warehouse , UpdateBatchStockRequest $request)
    {
       try {
            $this->authorize('manage', $warehouse);
            
             WarehouseProductBatch::where("product_id" ,$request->product_id)->where("warehouse_id" ,$warehouse->id )->where('batch_number' ,$request->batch_number )
               ->update(["stock"=>$request->stock]);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////

    public function destroy(Warehouse $warehouse,DeleteBatchRequest $request): ?\Illuminate\Http\JsonResponse
    {
        try {

            $this->authorize('manage', $warehouse);
            $batch = WarehouseProductBatch::where("batch_number",$request->batch_number)
            ->where("product_id",$request->product_id)
            ->where("warehouse_id",$warehouse->id)
            ->first();
            if ($batch ) {
                $batch->delete();
            }
            else{
                return JsonResponse::respondError("هذا الباتش غير متاح ");
            }
            

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }



    ///////////////////////////////////////////////////////////////////////////////////////////

    public function import(FileImportRequest $request, Warehouse $warehouse)
    {
        try {
          
            $import = new WarehouseProductBatchImport($warehouse);
            Excel::import($import, $request->file('file'));

            if ($import->failures()->isNotEmpty()) {
                return JsonResponse::respondError($import->failures());
            }

            return JsonResponse::respondSuccess( ' تم استيراد البيانات بنجاح');
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
        
    }

    //////////////////////////////////////////////////////////////////////////////////////////////

    private function handleData(WarehouseProductRequest $request,int $warehouse_id)
    {
        $expiry_date = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                ->format('Y-m-d');
        $data['warehouse_id'] = $warehouse_id;
        $data['product_id'] = $request->product_id;
        $data['stock'] = $request->stock;
        $data['expiry_date'] = $expiry_date;
        $data['batch_number'] = $request->batch_number;
        return $data;
    }






}
