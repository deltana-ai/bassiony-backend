<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\BaseController;

use App\Helpers\JsonResponse;
use App\Http\Requests\AssignEmployeeRoleRequest;
use App\Http\Requests\AssignWarehouseRequest;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\EmployeeResource;
use App\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class EmployeeController extends BaseController
{
    use AuthorizesRequests;

    protected mixed $crudRepository;

    public function __construct(EmployeeRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {

            $employee = EmployeeResource::collection($this->crudRepository->all(
                ["warehouse"],
                ["company_id"=>auth()->user()->company_id],
                ['*']
            ));
            return $employee->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(EmployeeRequest $request)
    {
        try {
            $data = $this->prepareData($request);
            $employee = $this->crudRepository->create($data);
            return new EmployeeResource($employee);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Employee $employee): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('manage', $employee);

            $employee->load(["warehouse"]);

            return JsonResponse::respondSuccess('Item Fetched Successfully', new EmployeeResource($employee));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(EmployeeRequest $request, Employee $employee)
    {
        try {
            $this->authorize('manage', $employee);
            $data = $this->prepareData($request);
            $this->crudRepository->update($data, $employee->id);
            activity()->performedOn($employee)->withProperties(['attributes' => $employee])->log('update');
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $employees = Employee::whereIn('id', $request->items)->get();

            foreach ($employees as $employee) {
                $this->authorize('manage', $employee); 
            }
            $this->crudRepository->deleteRecords('employees', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $employees = Employee::whereIn('id', $request->items)->get();

            foreach ($employees as $employee) {
                $this->authorize('manage', $employee); 
            }
            $this->crudRepository->restoreItem(Employee::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $employees = Employee::whereIn('id', $request->items)->get();

            foreach ($employees as $employee) {
                $this->authorize('manage', $employee); 
            }
            $this->crudRepository->deleteRecordsFinial(Employee::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function assignWarehouse(AssignWarehouseRequest $request)
    {
        try {
            $this->crudRepository->assignToWarehouse('employees', $request['items'] ,$request->warehouse_id);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }

    }

    public function assignRole(AssignEmployeeRoleRequest $request)
    {
        try {
            $this->crudRepository->assignToRole('employees', $request['items'] ,$request->role_id);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }

    }



    private Function prepareData(EmployeeRequest $request)
    {  
        if ($request->isMethod('post')) {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
        }
        else {
            $data = $request->except(['password', 'password_confirmation']);
        }
        
        $data['company_id'] = auth("employees")->user()->company_id??0;
        return $data;
    }

}
