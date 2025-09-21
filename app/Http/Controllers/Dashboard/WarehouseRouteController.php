<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\WarehouseRouteRequest;
use App\Http\Resources\WarehouseRouteResource ;
use App\Interfaces\WarehouseRouteRepositoryInterface;
use App\Models\WarehouseRoute;
use Exception;
use Illuminate\Http\Request;

class WarehouseRouteController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(WarehouseRouteRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $warehouse_routes = WarehouseRouteResource::collection($this->crudRepository->all(
                ["wharehouse"],
                [],
                ['*']
            ));
            return $warehouse_routes->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(WarehouseRouteRequest $request)
    {
            try {
                $warehouse_route = $this->crudRepository->create($request->validated());
                
                return new WarehouseRouteResource($warehouse_route);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(WarehouseRoute $warehouse_route): ?\Illuminate\Http\JsonResponse
    {
        try {
            $warehouse_route->load(['wharehouse']);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new WarehouseRouteResource($warehouse_route));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(WarehouseRouteRequest $request, WarehouseRoute $warehouse_route)
    {
        $this->crudRepository->update($request->validated(), $warehouse_route->id);

       
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('warehouse_routes', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

  





}
