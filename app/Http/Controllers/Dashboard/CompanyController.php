<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;
use App\Helpers\JsonResponse;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\{CompanyResource,EmployeeResource};
use App\Http\Resources\ProductResource;
use App\Interfaces\CompanyRepositoryInterface;
use App\Models\Company;
use App\Models\Product;
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

                $employee = $this->crudRepository->createCompanywithUser($request->validated());

                $employee->load("roles");

                return JsonResponse::respondSuccess('Item created Successfully', new EmployeeResource($employee));

            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show(Company $company): ?\Illuminate\Http\JsonResponse
    {
        try {

            $this->authorize('view', $company);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new CompanyResource($company));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(CompanyRequest $request, Company $company)
    {
        try {
            $this->authorize('update', $company);

            $employee = $this->crudRepository->updateCompanywithUser($request->validated(), $company->id);

            $employee->load("roles");

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY), new EmployeeResource($employee));


        } catch (\Throwable $th) {
            return JsonResponse::respondError($th->getMessage());
        }

    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('delete', Company::class);
            $this->crudRepository->deleteCompanywithUsers( $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('restore', Company::class);
            $this->crudRepository->restoreCompanywithUsers( $request['items']);
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





      public function availableProducts($companyId)
        {
      $company = Company::findOrFail($companyId);

        $products = Product::whereHas('warehouseBatches', function ($query) use ($companyId) {
                $query->where('stock', '>', 0)
                    ->whereHas('warehouse', function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    });
            })
            ->with(['warehouseBatches' => function ($q) use ($companyId) {
                $q->where('stock', '>', 0)
                ->whereHas('warehouse', function ($qq) use ($companyId) {
                    $qq->where('company_id', $companyId);
                })
                ->select('id', 'warehouse_id', 'product_id', 'batch_number', 'stock', 'expiry_date');
            }])
            ->get();

           $data = [
                'company' => $company->name,
                'products' => ProductResource::collection($products),
            ];
          return JsonResponse::respondSuccess('products Fetched Successfully', $data);

    }



}
