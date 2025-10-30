<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource ;
use App\Interfaces\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use Exception;
use Illuminate\Http\Request;
use SebastianBergmann\CodeUnit\FunctionUnit;

class WarehouseController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(WarehouseRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $warehouses = WarehouseResource::collection($this->crudRepository->all(
                ["company"],
                [],
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
            return JsonResponse::respondSuccess('Item Fetched Successfully', new WarehouseResource($warehouse));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {

        $data = $this->prepareData( $request);
        $this->crudRepository->update($data, $warehouse->id);

       
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('warehouses', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(Warehouse::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
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
