<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\BranchProductRequest;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource ;
use App\Interfaces\BranchRepositoryInterface;
use App\Models\Branch;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BranchController extends BaseController
{
    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(BranchRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $branches = BranchResource::collection($this->crudRepository->all(
                ["pharmacy:id,name"],
                ["pharmacy_id"=>auth()->user()->pharmacy_id],
                ['*']
            ));
            return $branches->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(BranchRequest $request)
    {
            try {
                $branch = $this->crudRepository->create($request->validated());
                
                return new BranchResource($branch);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Branch $branch): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('manage', $branch); 

            $branch->load([ 'pharmacy:id,name','products']);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new BranchResource($branch));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }



    public function update(BranchRequest $request, Branch $branch)
    {
        $this->authorize('manage', $branch); 

        $this->crudRepository->update($request->validated(), $branch->id);

       
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
    }


 

    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $branches = Branch::whereIn('id', $request->items)->get();

            foreach ($branches as $branch) {
                $this->authorize('manage', $branch); 
            }
            $this->crudRepository->deleteRecords('branches', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $branches = Branch::whereIn('id', $request->items)->get();

            foreach ($branches as $branch) {
                $this->authorize('manage', $branch); 
            }
            $this->crudRepository->restoreItem(Branch::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $branches = Branch::whereIn('id', $request->items)->get();

            foreach ($branches as $branch) {
                $this->authorize('manage', $branch); 
            }
            $this->crudRepository->deleteRecordsFinial(Branch::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }





}
