<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\EmployeeResource;
use Exception;
use App\Models\Employee;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use App\Notifications\ResetPasswordTokenNotification;
class EmployeeProfileController extends Controller
{

    public function show()
    {
        try {
            $employee = auth('employees')->user();
            return JsonResponse::respondSuccess(__('profile fetched successfully'), new EmployeeResource($employee));

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), 401);
        }
    }
    public function updatePassword(UpdatePasswordRequest $request)
    {
        
        try {
            $user = auth('employees')->user();
            $employee = Employee::where('email', $user->email)->first();
            if (Hash::check($request->current_password, $employee->password)) {

                $employee->update([
                    'password' => Hash::make($request->password),
                ]);

               return JsonResponse::respondSuccess(__('password updated successfully'), new EmployeeResource($user));


            }
            else {
                return JsonResponse::respondError('Current password is not correct', 401);
            }

        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), 401);
        }
    }

    public function updateProfile( UpdateProfileRequest $request)
    {
        try {
            $user = auth("employees")->user();
            $data = $request->except(['password']);
            $user->update($data);

            return JsonResponse::respondSuccess(__('profile update successfully'), new EmployeeResource($user));
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), $e->getCode() ?: 400);
        }
    }


     public function logout()
    {
        try {
            auth('employees')->user()->tokens()->delete();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage(), 401);
        }
    }


    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
        $credentials = $request->only('email', 'password');
       
        $employee = Employee::where('email', $credentials['email'])->first();
        if ($employee) {
            if (Hash::needsRehash($employee->password)) {
                $employee->password = Hash::make($credentials['password']);
                $employee->save();
            }
            if (Hash::check($credentials['password'], $employee->password)) {
                activity()->performedOn($employee)->withProperties(['attributes' => $employee])->log('login');
                $token = $employee->createToken('emoloyee-token')->plainTextToken;
                return response()->json([
                    'data' => new EmployeeResource($employee),
                    'token' => $token,
                ]);
            }
        }
        return JsonResponse::respondError('Error Data Not Match', 401);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

   
    

   

    public function forgotPassword(Request $request)
    {
        try{
            $request->validate(['email' => 'required|email']);

            $user = Employee::where('email', $request->email)->first();

            if (!$user) {
                return JsonResponse::respondError("No user found with this email", 404);
            }

            $token = Password::broker('employees')->createToken($user);

            $user->notify(new ResetPasswordTokenNotification($token, $user->email));

            return JsonResponse::respondSuccess('Password reset token sent to your email.');
        }
        catch (Exception $e) {
            
            return JsonResponse::respondError($e->getMessage());
        }
    }




    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'token'    => 'required|string',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $status = Password::broker('employees')->reset(
                $request->only('email', 'token', 'password', 'password_confirmation'),
                function ($user, $password) {
                    $user->forceFill([
                        'password'       => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            return JsonResponse::respondSuccess(__($status));
        }
        catch (Exception $e) {
            
            return JsonResponse::respondError($e->getMessage());
        }
    }



}
