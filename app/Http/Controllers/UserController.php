<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\LogResource;
use App\Http\Resources\UserIndexResource;
use App\Http\Resources\UserResource;
use App\Interfaces\UserRepositoryInterface;
use App\Mail\UserMail;
use App\Mail\UserRequestMail;
use App\Models\ContactPeople;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class UserController extends BaseController
{
    protected mixed $crudRepository;

    public function __construct(UserRepositoryInterface $pattern)
    {
        $this->crudRepository = $pattern;
    }

    public function index()
    {
        try {
            $user = UserResource::collection($this->crudRepository->all(
                [],
                [],
                ['*']
            ));
            return $user->additional(JsonResponse::success());
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
    public function show(User $user): ?\Illuminate\Http\JsonResponse
    {
        try {
            return JsonResponse::respondSuccess('Item fetched successfully', new UserResource($user));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function update(UserRequest $request, User $user)
    {
        try {
            $this->crudRepository->update($request->validated(), $user->id);
            if (request('image') !== null) {
                $user = User::find($user->id);
                $image = $this->crudRepository->AddMediaCollection('image', $user);
            }
            activity()->performedOn($user)->withProperties(['attributes' => $user])->log('update');
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function destroy(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecords('users', $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function restore(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->restoreItem(User::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_RESTORED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function forceDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->crudRepository->deleteRecordsFinial(User::class, $request['items']);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_FORCE_DELETED_SUCCESSFULLY));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        try {
            $user = $this->crudRepository->createUser($validated);

            $token = $user->createToken('user-token')->plainTextToken;

            return response()->json([
                'status'  => true,
                'user'    => new UserResource($user),
                'message' => 'User Registered Successfully',
                'token'   => $token
            ], 200);

        } catch (\Throwable $th) {
            return JsonResponse::respondError($th->getMessage());
        }
    }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required',
                'password' => 'required',
            ]);

            $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $user = User::where($fieldType, $request->login)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return JsonResponse::respondError('The provided credentials are incorrect.');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'user' => new UserResource($user),
                'message' => 'User Logged In Successfully',
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
            $user = User::where($fieldType, $request->login)->first();

            if (!$user) {
                return JsonResponse::respondError('User not found.');
            }
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return JsonResponse::respondSuccess('Password updated successfully');
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function logout(Request $request)
    {
        $request->user('users')->tokens()->delete();

        return JsonResponse::respondSuccess('Successfully logged out');
    }


    public function checkPhone(Request $request)
{
    try {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $exists = User::where('phone', $request->phone)->exists();

        if ($exists) {
            return JsonResponse::respondSuccess('Phone number already exists', ['exists' => true]);
        } else {
            return JsonResponse::respondSuccess('Phone number not found', ['exists' => false]);
        }
    } catch (Exception $e) {
        return JsonResponse::respondError($e->getMessage());
    }
}

}   
