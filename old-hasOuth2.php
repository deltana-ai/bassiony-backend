<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Traits\OtpAuthenticates;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

trait MultiAuth
{

   use OtpAuthenticates;

   /*
   * register as client or  pharmacist, driver
   * return JsonResponse
   */

    public function register($request, $modelClass,$guard)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.(new $modelClass)->getTable()],
            'phone' => ['required', 'regex:/^[0-9]{10,15}$/', 'unique:'.(new $modelClass)->getTable()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

        ]);

        $data['password'] = Hash::make($data['password']);
        $model = $modelClass::create($data);

        $this->generateAndSendOtp($model->email, $model->name ,$guard);

        return response()->json(['message' => 'OTP verification required',403]);
    }
    //////////////////////////////////////////////////////////////////////////////
    /*
    * login as client or  pharmacist, driver
    * return JsonResponse
    */
    public function login($request , $modelClass,$guard)
    {
        $data = $request->validate([
           'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
           'password' => ['required', Rules\Password::defaults()],
        ]);

        $model = $modelClass::where('email', $data['email'])->first();

        if (!$model || !Hash::check($data['password'], $model->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }


        $this->generateAndSendOtp($model->email, $model->name ,$guard);

        return response()->json(['message' => 'OTP verification required ',403]);
    }
    /////////////////////////////////////////////////////////////////////////////////////////
    /*
    * forgotPassword  client or  pharmacist, driver
    * return JsonResponse
    */
    public function forgotPassword( $request ,$modelClass ,$guard)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);
        $model = $modelClass::where('email', $data['email'])->first();
        if (!$model) return response()->json(['message' => 'Email not found'], 404);

        $this->generateAndSendOtp($model->email, $model->name ,$guard);

        return response()->json(['message' =>'Reset OTP sent to your email']);
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    /*
    * reset password  client or  pharmacist, driver
    * return JsonResponse
    */
    public function resetPassword( $request ,$modelClass, $guard )
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'otp' => ['required','numeric','min:1000','max:9999'],
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $result = $this->checkOtp( $request->email, $request->otp, $guard );

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }

        $model = $modelClass::where('email', $data['email'])->first();
        $model->update(['password' => Hash::make($data['new_password'])]);

        return response()->json(['message' => 'Password reset successfully']);
    }
    ////////////////////////////////////////////////////////////////////////////////
    /*
    * verify otp
    */
    public function verifyOtp( $request, $modelClass, $guard )
    {
        $result = $this->checkOtp( $request->email, $request->otp, $guard );

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }

        $model = $modelClass::where('email', $request->email)->first();
        $model->update(['is_verified' => true]);
        $token = $model->createToken($guard .'-token',[$guard])->plainTextToken;

        return response()->json([
            'message' => 'verification successfully',
            'token' => $token,
            'user' => $model
        ]);
    }

    ///////////////////////////////////////////////////////////////////////////

    public function logout( $request,$guard)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $accessToken->delete();

        return response()->json(['message' => 'logout_success']);
    }



}
