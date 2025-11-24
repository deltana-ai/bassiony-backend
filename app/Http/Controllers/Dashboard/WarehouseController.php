<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource ;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use SebastianBergmann\CodeUnit\FunctionUnit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WarehouseController extends BaseController
{
    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(WarehouseRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->middleware('auth:employees');
      //  $this->middleware('permission:warehouse-list|manage-company', ['only' => ['index','show']]);
        // $this->middleware('permission:warehouse-create|manage-company', ['only' => ['store']]);
        // $this->middleware('permission:warehouse-edit|manage-company', ['only' => [ 'update']]);
        // $this->middleware('permission:warehouse-delete|manage-company', ['only' => ['destroy','restore','forceDelete']]);
    
    }

    public function index()
    {
        try {

            $warehouses = WarehouseResource::collection($this->crudRepository->all(
                ["company"],
                ["company_id"=>auth()->guard("employees")->user()->company_id],
                ['*']
            ));
            return $warehouses->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(WarehouseRequest $request)
    {
            try {
                $data = $this->prepareData( $request);
                $warehouse = $this->crudRepository->create($data);
               
                return new WarehouseResource($warehouse);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Warehouse $warehouse): ?\Illuminate\Http\JsonResponse
    {
        try {
            $warehouse->load([ 'company','products']);
            $this->authorize('manage', $warehouse);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new WarehouseResource($warehouse));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $this->authorize('manage', $warehouse);

        $data = $this->prepareData( $request);
        $this->crudRepository->update($data, $warehouse->id);

       
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $warehouses = Warehouse::whereIn('id', $request->items)->get();

            foreach ($warehouses as $warehouse) {
                $this->authorize('manage', $warehouse); 
            }
            $this->crudRepository->deleteRecords('warehouses', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $warehouses = Warehouse::whereIn('id', $request->items)->get();

            foreach ($warehouses as $warehouse) {
                $this->authorize('manage', $warehouse); 
            }
            $this->crudRepository->restoreItem(Warehouse::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $warehouses = Warehouse::whereIn('id', $request->items)->get();

            foreach ($warehouses as $warehouse) {
                $this->authorize('manage', $warehouse); 
            }
            $this->crudRepository->deleteRecordsFinial(Warehouse::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    private Function prepareData(WarehouseRequest $request)
    {  
        $data = $request->validated();
        $data['company_id'] = auth("employees")->user()->company_id??0;
        return $data;
    }





}
