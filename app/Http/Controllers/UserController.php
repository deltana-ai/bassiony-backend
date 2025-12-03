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



    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Email not found',
                'user' => null,
                'token' => null
            ], 404);
        }

        $otp = rand(100000, 999999);
        $user->update(['otp' => $otp]);

        Mail::raw("Your OTP code is: $otp", function ($m) use ($user) {
            $m->to($user->email)->subject('Password Reset OTP');
        });

        return response()->json([
            'result' => true,
            'status' => 200,
            'message' => 'OTP sent successfully',
            'user' => new UserResource($user),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $user = User::where([
            'email' => $request->email,
            'otp' => $request->otp
        ])->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP',
                'user' => null,
                'token' => null
            ], 400);
        }

        $user->otp = null;
        $user->save();

        $token = $user->createToken('reset-password-token')->plainTextToken;

        return response()->json([
            'result' => true,
            'status' => 200,
            'message' => 'OTP verified successfully',
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'user' => null,
                'token' => null
            ], 401);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'result' => true,
            'status' => 200,
            'message' => 'Password reset successfully',
            'user' => new UserResource($user),
        ]);
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'user' => null,
                'token' => null
            ], 401);
        }

         if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect',
                'user' => null,
                'token' => null
            ], 400);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'result' => true,
            'status' => 200,
            'message' => 'Password updated successfully',
            'user' => new UserResource($user),
        ]);
    }


    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'user' => null,
                'token' => null
            ], 401);
        }

        $user->delete();

        return response()->json([
            'result' => true,
            'status' => 200,
            'message' => 'Account deleted successfully',
            'user' => null,
        ]);
    }

    public function checkAuth(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'user' => null,
                'token' => null
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'User is authenticated',
            'user' => new UserResource($user),
            'token' => null
        ]);
    }


}
