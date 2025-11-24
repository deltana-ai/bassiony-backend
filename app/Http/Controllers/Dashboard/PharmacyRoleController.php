<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\Pharmacist;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class PharmacyRoleController extends Controller
{
    protected mixed $crudRepository;

    public function __construct(RoleRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->middleware('auth:pharmacists');
        // $this->middleware('permission:role-list|manage-pharmacy', ['only' => ['index','show']]);
        // $this->middleware('permission:role-create|manage-pharmacy', ['only' => [ 'store']]);
        // $this->middleware('permission:role-edit|manage-pharmacy', ['only' => [ 'update']]);
        // $this->middleware('permission:role-delete|manage-pharmacy', ['only' => ['destroy','restore','forceDelete']]);

    }

    public function index()
    {
        try {

            $roles = RoleResource::collection($this->crudRepository->all(
                [],
                ["guard_name" => auth()->guard("pharmacists")->user()->guard_name,"pharmacy_id"=>auth()->guard("pharmacists")->user()->pharmacy_id],
                ['*']
            ));
            return $roles->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(RoleRequest $request)
    {
            try {
                $role = $this->crudRepository->createRole($request->validated());
               
               return JsonResponse::respondSuccess('Item created Successfully');

            } catch (Exception $e) {
                return JsonResponse::respondError($e->getMessage());
            }
    }

    public function show( $id)
    {
        try {
            
            $role = $this->crudRepository->find($id);
           
            if(!$role){
                return JsonResponse::respondError(trans(JsonResponse::MSG_NOT_FOUND));
            }
            if ($role->guard_name !== auth()->guard("pharmacists")->user()->guard_name || $role->pharmacy_id!== auth()->guard("pharmacists")->user()->pharmacy_id ) {
               
                return JsonResponse::respondError(trans(JsonResponse::MSG_NOT_AUTHORIZED));

            }
            $role->load(['permissions']);
            return JsonResponse::respondSuccess('Item Fetched Successfully', new RoleResource($role));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(RoleRequest $request, $id)
    {
        try {
            $role = $this->crudRepository->find($id);
            if(!$role){
                return JsonResponse::respondError(trans(JsonResponse::MSG_NOT_FOUND));
            }
            
            if($role->name === 'pharmacy_owner' ){
                return JsonResponse::respondError(trans(JsonResponse::MSG_NOT_AUTHORIZED));
            }
            if ($role->guard_name !== auth()->guard("pharmacists")->user()->guard_name || $role->pharmacy_id !== auth()->guard("pharmacists")->user()->pharmacy_id ) {
               
                return JsonResponse::respondError(trans(JsonResponse::MSG_NOT_AUTHORIZED));

            }
            $this->crudRepository->updateRole( $role ,$request->validated());
       
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));

        } catch (\Throwable $th) {
            return JsonResponse::respondError($th->getMessage());
        }
        
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
           
            $this->crudRepository->deleteRoles($request['items'],Pharmacist::class);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

   


    public function getPermissions(Request $request)
    {
        try {
            $permissions = RoleResource::collection($this->crudRepository->getPermissions());
            return $permissions->additional(JsonResponse::success());

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    


}
