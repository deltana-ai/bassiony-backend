<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\Admin;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    protected mixed $crudRepository;

    public function __construct(RoleRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->middleware('auth:admins');
        $this->middleware('permission:role-list|manage-site', ['only' => ['index']]);
        $this->middleware('permission:role-create|manage-site', ['only' => [ 'store']]);
        $this->middleware('permission:role-edit|manage-site', ['only' => [ 'update']]);
        $this->middleware('permission:role-delete|manage-site', ['only' => ['destroy','restore','forceDelete']]);

    }

    public function index()
    {
        try {

            $roles = RoleResource::collection($this->crudRepository->all(
                [],
                ["guard_name" => auth()->guard("admins")->user()->guard_name],
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
               
                return new RoleResource($role);
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
            if ($role->guard_name !== auth()->guard("admins")->user()->guard_name ) {
               
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
            if($role->name === 'site_owner' ){
                return JsonResponse::respondError(trans(JsonResponse::MSG_NOT_AUTHORIZED));
            }
            if ($role->guard_name !== auth()->guard("admins")->user()->guard_name ) {
               
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
           
            $this->crudRepository->deleteRoles($request['items'],Admin::class);
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
