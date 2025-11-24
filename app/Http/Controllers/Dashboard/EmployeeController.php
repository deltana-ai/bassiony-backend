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
use Illuminate\Support\Facades\DB;

class EmployeeController extends BaseController
{
    use AuthorizesRequests;

    protected mixed $crudRepository;

    public function __construct(EmployeeRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->middleware('auth:employees');
        $this->middleware('permission:employee-list|manage-company', ['only' => ['index']]);
        $this->middleware('permission:employee-create|manage-company', ['only' => [ 'store']]);
        $this->middleware('permission:employee-edit|manage-company', ['only' => [ 'update','assignWarehouse','assignRole']]);
        $this->middleware('permission:employee-delete|manage-company', ['only' => ['destroy','restore','forceDelete']]);

    }

    public function index()
    {
        try {

            $employee = EmployeeResource::collection($this->crudRepository->all(
                ["warehouses"],
                ["company_id"=>auth()->guard("employees")->user()->company_id],
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
            
            $employee = $this->crudRepository->createEmployee($data);
            
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Employee $employee): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('manage', $employee);

            $employee->load(["warehouses"]);

            return JsonResponse::respondSuccess("تم جلب بيانات الموظف بنجاح", new EmployeeResource($employee));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(EmployeeRequest $request, Employee $employee)
    {
        try {
            $this->authorize('manage', $employee);
            $data = $this->prepareData($request);
            $this->crudRepository->updateEmployee( $employee,$data);
            activity()->performedOn($employee)->withProperties(['attributes' => $employee])->log('update');
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $company_id = auth()->guard("employees")->user()->company_id;
            $employees = Employee::whereIn('id', $request->items)->where("company_id",$company_id)->get();
            if(count($request['items']) != $employees->count()){
                return JsonResponse::respondError("يوجد موظفين غير موجودة");
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
            $company_id = auth()->guard("employees")->user()->company_id;
            $employees = Employee::whereIn('id', $request->items)->where("company_id",$company_id)->get();
            if(count($request['items']) != $employees->count()){
                return JsonResponse::respondError("يوجد موظفين غير موجودة");
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
            $company_id = auth()->guard("employees")->user()->company_id;
            $employees = Employee::whereIn('id', $request->items)->where("company_id",$company_id)->get();
            if(count($request['items']) != $employees->count()){
                return JsonResponse::respondError("يوجد موظفين غير موجودة");
            }
            DB::beginTransaction();
            foreach ($employees as $employee) {
                $employee->roles()->detach();
                $employee->warehouses()->detach();
            }
            $this->crudRepository->deleteRecordsFinial(Employee::class, $request['items']);
            DB::commit();
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollback();
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function assignWarehouse(AssignWarehouseRequest $request)
    {
        try {
            $this->crudRepository->assignToWarehouse( $request['items'] ,$request->warehouse_id);
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
