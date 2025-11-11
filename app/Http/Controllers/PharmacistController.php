<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\Auth\RegisterPharmacistRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\PharmacistRequest;
use App\Http\Resources\PharmacistResource;
use App\Interfaces\PharmacistRepositoryInterface;
use App\Models\Pharmacist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PharmacistController extends BaseController
{
    use AuthorizesRequests;
    protected mixed $crudRepository;

    public function __construct(PharmacistRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
        $this->crudRepository = $pattern;
        $this->middleware('permission:pharmacist-list|manage-site', ['only' => ['index']]);
        $this->middleware('permission:pharmacist-create|manage-site', ['only' => [ 'store']]);
        $this->middleware('permission:pharmacist-edit|manage-site', ['only' => [ 'update']]);
        $this->middleware('permission:pharmacist-delete|manage-site', ['only' => ['destroy','restore','forceDelete']]);
    }

    public function index()
    {
        try {
            $pharmacist = PharmacistResource::collection($this->crudRepository->all(
                [],
                ["pharmacy_id"=>auth()->guard("pharmacists")->user()->pharmacy_id],
                ['*']
            ));
            return $pharmacist->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function store(PharmacistRequest $request)
    {
        try {
            
            $role = Role::where('id',$request->role_id)->where('guard_name',"pharmacists")->first();

            if (!$role) {

                throw new \Exception("الدور الوظيفي غير موجود");
            }
            $pharmacist = $this->crudRepository->create($request->except(["role_id"]));
            if (request('image') !== null) {
                $this->crudRepository->AddMediaCollection('image', $pharmacist);
            }
            $pharmacist->assignRole($role);
            return new PharmacistResource($pharmacist);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function show(Pharmacist $pharmacist): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('manage', $pharmacist);

            return JsonResponse::respondSuccess('Item fetched successfully', new PharmacistResource($pharmacist));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


   public function update(PharmacistRequest $request, Pharmacist $pharmacist)
    {
        try {
            $this->authorize('manage', $pharmacist);

            $data = $request->validated();
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            unset($data['role_id']);
            $role = Role::where('id',$request->role_id)->where('guard_name',"pharmacists")->first();
            if (!$role) {

                throw new \Exception("الدور الوظيفي غير موجود");
            }
            $this->crudRepository->update($data, $pharmacist->id);

            $pharmacist->syncRoles($role);
            if ($request->hasFile('image')) {
                $pharmacist = Pharmacist::find($pharmacist->id);
                $this->crudRepository->AddMediaCollection('image', $pharmacist);
            }
            activity()
                ->performedOn($pharmacist)
                ->withProperties(['attributes' => $pharmacist])
                ->log('update');

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $pharmacy_id = auth()->guard("pharmacists")->user()->pharmacy_id;
            $pharmacists = Pharmacist::whereIn('id', $request->items)->where("pharmacy_id",$pharmacy_id)->get();
            if(count($request['items']) != $pharmacists->count()){
                return JsonResponse::respondError("يوجد صيادلة غير موجودة");
            }
            $this->crudRepository->deleteRecords('pharmacists', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $pharmacy_id = auth()->guard("pharmacists")->user()->pharmacy_id;
            $pharmacists = Pharmacist::whereIn('id', $request->items)->where("pharmacy_id",$pharmacy_id)->get();
            if(count($request['items']) != $pharmacists->count()){
                return JsonResponse::respondError("يوجد صيادلة غير موجودة");
            }
            $this->crudRepository->restoreItem(Pharmacist::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $pharmacy_id = auth()->guard("pharmacists")->user()->pharmacy_id;
            $pharmacists = Pharmacist::whereIn('id', $request->items)->where("pharmacy_id",$pharmacy_id)->get();
            if(count($request['items']) != $pharmacists->count()){
                return JsonResponse::respondError("يوجد صيادلة غير موجودة");
            }
            foreach ($pharmacists as $pharmacist) {
                $pharmacist->roles()->detach();
            }
            $this->crudRepository->deleteRecordsFinial(Pharmacist::class, $request['items']);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    // public function register(RegisterPharmacistRequest $request)
    // {
    //     $validated = $request->validated();

    //     try {
    //         $pharmacist = $this->crudRepository->createPharmacist($validated);

    //         $token = $pharmacist->createToken('user-token')->plainTextToken;

    //         return response()->json([
    //             'status'  => true,
    //             'pharmacist'    => new PharmacistResource($pharmacist),
    //             'message' => 'User Registered Successfully',
    //             'token'   => $token
    //         ], 200);

    //     } catch (\Throwable $th) {
    //         return JsonResponse::respondError($th->getMessage());
    //     }
    // }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required',
                'password' => 'required',
            ]);
            $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $pharmacist = Pharmacist::where($fieldType, $request->login)->first();
            if (!$pharmacist || !Hash::check($request->password, $pharmacist->password)) {
                return JsonResponse::respondError('The provided credentials are incorrect.');
            }
            $token = $pharmacist->createToken('pharmacist_token')->plainTextToken;
            return response()->json([
                'status' => true,
                'pharmacist' => new PharmacistResource($pharmacist),
                'message' => 'Pharmacist Logged In Successfully',
                'token' => $token
            ]);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);

            $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $pharmacist = Pharmacist::where($fieldType, $request->login)->first();

            if (!$pharmacist) {
                return JsonResponse::respondError('Pharmacist not found.');
            }
            $pharmacist->update([
                'password' => Hash::make($request->password)
            ]);

            return JsonResponse::respondSuccess('Password updated successfully');
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function logout(Request $request)
    {
        $user = $request->user('pharmacists'); // مهم جداً تحدد الجارد
        if ($user) {
            $user->tokens()->delete();
        }

        return JsonResponse::respondSuccess('Successfully logged out');
    }


}
