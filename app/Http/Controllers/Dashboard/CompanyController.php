<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Interfaces\CompanyRepositoryInterface;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyController extends BaseController
{
    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(CompanyRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {
            $this->authorize('viewAny', Company::class);
            $companies = CompanyResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $companies->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(CompanyRequest $request)
    {
            try {

                $this->authorize('create', Company::class);
                $company = $this->crudRepository->create($request->validated());
                
                return new CompanyResource($company);
            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Company $company): ?\Illuminate\Http\JsonResponse
    {
        try {
            if(Auth()->guard('employees')->check()) {
              $company = $this->crudRepository->find(auth("employees")->user()->company_id);
            }
            $this->authorize('view', $company);
            
            return JsonResponse::respondSuccess('Item Fetched Successfully', new CompanyResource($company));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(CompanyRequest $request, Company $company)
    {
        try {
            if(Auth()->guard('employees')->check()) {
              $company = $this->crudRepository->find(auth("employees")->user()->company_id);
            }
            $this->authorize('update', $company);
            $this->crudRepository->update($request->validated(), $company->id);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));

        } catch (\Throwable $th) {
            return JsonResponse::respondError($th->getMessage());
        }
        
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('delete', Company::class);
            $this->crudRepository->deleteRecords('companies', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('restore', Company::class);
            $this->crudRepository->restoreItem(Company::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('forceDelete', Company::class);
            $this->crudRepository->deleteRecordsFinial(Company::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }





}
