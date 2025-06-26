<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pharmacist;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\MultiAuth;
class PharmacistAuthController extends Controller
{
    use MultiAuth;
    public function pharmacistRegister(Request $request)
    {
       return $this->register($request, Pharmacist::class,"pharmacist");
    }

    public function pharmacistLogin(Request $request)
    {
        return $this->login($request, Pharmacist::class,"pharmacist");
    }

    public function pharmacistForgotPassword(Request $request)
    {
       return $this->forgotPassword($request, Pharmacist::class,"pharmacist");
    }

    public function pharmacistResetPassword(Request $request)
    {
       return $this->resetPassword($request, Pharmacist::class,"pharmacist");
    }

    public function pharmacistVerify(Request $request)
    {
       return $this->verifyOtp($request, Pharmacist::class,"pharmacist");
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('auth.logout_success')]);
    }
}
