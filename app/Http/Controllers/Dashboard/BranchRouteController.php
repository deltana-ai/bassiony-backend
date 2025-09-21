<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\BranchRouteRequest;
use App\Http\Resources\BranchRouteResource ;
use App\Interfaces\BranchRouteRepositoryInterface;
use App\Models\BranchRoute;
use Exception;
use Illuminate\Http\Request;

class BranchRouteController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(BranchRouteRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $branch_routes = BranchRouteResource::collection($this->crudRepository->all(
                ["branch"],
                [],
                ['*']
            ));
            return $branch_routes->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(BranchRouteRequest $request)
    {
            try {
                $branch_route = $this->crudRepository->create($request->validated());
                
                return new BranchRouteResource($branch_route);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(BranchRoute $branch_route): ?\Illuminate\Http\JsonResponse
    {
        try {
            $branch_route->load(['branch']);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new BranchRouteResource($branch_route));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(BranchRouteRequest $request, BranchRoute $branch_route)
    {
        $this->crudRepository->update($request->validated(), $branch_route->id);

       
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('branch_routes', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }










}
