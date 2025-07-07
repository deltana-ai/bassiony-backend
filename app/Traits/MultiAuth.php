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
use App\Services\FirebaseAuthService;
use App\Traits\HttpResponses;
use App\Helpers\JsonResponse;
trait MultiAuth
{

   use HttpResponses;

   /*
   * register as client or  pharmacist, driver
   * return JsonResponse


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
*/
    //////////////////////////////////////////////////////////////////////////////
    /*
    * login as client or  pharmacist, driver
    * return JsonResponse

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
    }*/
    /////////////////////////////////////////////////////////////////////////////////////////
    /*
    * forgotPassword  client or  pharmacist, driver
    * return JsonResponse

    public function forgotPassword( $request ,$modelClass ,$guard)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);
        $model = $modelClass::where('email', $data['email'])->first();
        if (!$model) return response()->json(['message' => 'Email not found'], 404);

        $this->generateAndSendOtp($model->email, $model->name ,$guard);

        return response()->json(['message' =>'Reset OTP sent to your email']);
    } */

    /////////////////////////////////////////////////////////////////////////////////////////
    /*
    * reset password  client or  pharmacist, driver
    * return JsonResponse

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
    }*/
    ////////////////////////////////////////////////////////////////////////////////
    /*
    * verify otp

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
    }*/

    ///////////////////////////////////////////////////////////////////////////

    public function logout( $request,$guard)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->error(null, "Unauthenticated",  401);
        }

        $token = $request->bearerToken();

        if (!$token) {
          return $this->error(null, "No token provided",  401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return $this->error(null, "Invalid token",  401);

        }

        $accessToken->delete();
        return $this->success(null, "logout_success",  200);


    }


/////////////////////////////////////////////////////////////////////////
    public function login($request, $modelClass, $guard)
    {


        $request->validate([
            'firebase_token' => 'required|string',
        ]);
        $firebaseAuth = new FirebaseAuthService(app('firebase.auth')) ;
        $firebaseUser = $firebaseAuth->verifyToken($request->firebase_token);


        if (!$firebaseUser || !$firebaseUser['phone']) {

          return $this->error(null, "Invalid Firebase token or missing phone number",  401);
         
        }

        $uid = $firebaseUser['uid'];
        $phone = $firebaseUser['phone'];
        $name = $firebaseUser['name'] ?? 'new user';

        $model = $modelClass::where('firebase_uid', $uid)->first();

        if (!$model) {
          return $this->error(null, "User not found, please register first",  404);
        }

        $token = $model->createToken($guard . '-token', [$guard])->plainTextToken;
        $data['token'] = $token;
        $data['user'] = $model;
        return $this->success($data, "Logged in successfully",  200);


    }

    //////////////////////////////////////////////////////////
    public function register(Request $request, $modelClass, $guard)
    {
        $firebaseAuth = new FirebaseAuthService ;

        $request->validate([
            'firebase_token' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . (new $modelClass)->getTable(),
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $firebaseUser = $firebaseAuth->verifyToken($request->firebase_token);

        if (!$firebaseUser || !$firebaseUser['phone']) {

          return $this->error(null, "Invalid Firebase token or missing phone number",  401);

        }

        $model = $modelClass::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $firebaseUser['phone'],
            'firebase_uid' => $firebaseUser['uid'],
            'is_verified' => true,
        ]);

        $token = $model->createToken($guard . '-token', [$guard])->plainTextToken;
        $data['token'] = $token;
        $data['user'] = $model;
        return $this->success($data, "Account created successfully",  200);

    }

    ////////////////////////////////////////////////////////////////////////////////
    public function logoutAll(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->tokens()->delete();

        return response()->json(['message' => 'Logged out from all devices']);
    }




}
