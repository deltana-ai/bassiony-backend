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
use Exception;

trait MultiAuth
{

   use HttpResponses;

   /*
   * register as client or  pharmacist, driver
   * return JsonResponse
   */

   public function register( $request, $modelClass, $guard)
   {

       $request->validate([
           'firebase_token' => 'required|string',
           'name' => 'required|string|max:255',
           'email' => 'required|email|unique:' . (new $modelClass)->getTable(),
           'password' => ['required', 'confirmed', Rules\Password::defaults()],
       ]);
       try {
           $firebaseAuth = new FirebaseAuthService ;

           $firebaseUser = $firebaseAuth->verifyToken($request->firebase_token);

           if (!$firebaseUser || !$firebaseUser['phone']) {

             return $this->error(null, "Invalid Firebase token or missing phone number",  401);

           }
           if ($modelClass::where('firebase_uid', $firebaseUser['uid'])->exists()) {
               return $this->error(null, "This Firebase UID is already registered.", 409);
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
           return JsonResponse::respondSuccess('Account created successfully', $data);
       } catch (Exception $e) {
           return JsonResponse::respondError($e->getMessage());
       }



   }


/////////////////////////////////////////////////////////////////////////
    public function login($request, $modelClass, $guard)
    {


        $request->validate([
            'firebase_token' => 'required|string',
        ]);
         try {
            $firebaseAuth = new FirebaseAuthService(app('firebase.auth')) ;
            $firebaseUser = $firebaseAuth->verifyToken($request->firebase_token);


            if (!$firebaseUser || !$firebaseUser['phone']) {

              return $this->error(null, "Invalid Firebase token or missing phone number",  401);

            }

            $uid = $firebaseUser['uid'];
            $phone = $firebaseUser['phone'];

            $model = $modelClass::where('firebase_uid', $uid)->first();

            if (!$model) {
              return $this->error(null, "User not found, please register first",  404);
            }

            $token = $model->createToken($guard . '-token', [$guard])->plainTextToken;
            $data['token'] = $token;
            $data['user'] = $model;
            $model->last_login_at = now();
            $model->save();
            return JsonResponse::respondSuccess('Logged in successfully', $data);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }

    }

    //////////////////////////////////////////////////////////

    public function logout( $request, $guard)
    {
        $user = auth($guard)->user();

        if (!$user) {
            return JsonResponse::respondError("Unauthenticated",401);
        }

        $token = $request->bearerToken();

        if (!$token) {

          return JsonResponse::respondError("No token provided",401);

        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || $accessToken->tokenable_id !== $user->id || $accessToken->tokenable_type !== get_class($user)) {
            return JsonResponse::respondError("Invalid token",401);

        }

        $accessToken->delete();
        return JsonResponse::respondSuccess('Logged out successfully');

    }

    ////////////////////////////////////////////////////////////////////////////////
    // public function logoutAll( $request)
    // {
    //     $user = auth()->user();
    //
    //     if (!$user) {
    //         return response()->json(['message' => 'Unauthenticated'], 401);
    //     }
    //
    //     $user->tokens()->delete();
    //
    //     return response()->json(['message' => 'Logged out from all devices']);
    // }




}
