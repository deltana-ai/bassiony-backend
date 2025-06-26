<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\MultiAuth;

class ClientAuthController extends Controller
{

    use MultiAuth;

    public function clientRegister(Request $request)
    {
      return  $this->register($request, User::class ,"client");
    }

    public function clientLogin(Request $request)
    {
       return $this->login($request, User::class ,"client");
    }

    public function clientForgotPassword(Request $request)
    {
      return $this->forgotPassword($request, User::class,"client");
    }

    public function clientResetPassword(Request $request)
    {
       return $this->resetPassword($request, User::class ,"client");
    }

    public function clientVerify(Request $request)
    {
       return $this->verifyOtp($request, User::class,"client");
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('auth.logout_success')]);
    }
}
