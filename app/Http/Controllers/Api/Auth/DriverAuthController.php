<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\MultiAuth;
use Illuminate\Support\Facades\Auth;

class DriverAuthController extends Controller
{
    use MultiAuth;
    public function driverRegister(Request $request)
    {
       return $this->register($request, Driver::class ,"driver");
    }

    public function driverLogin(Request $request)
    {
       return $this->login($request, Driver::class ,"driver");
    }

    // public function driverForgotPassword(Request $request)
    // {
    //   return  $this->forgotPassword($request, Driver::class,"driver");
    // }
    //
    // public function driverResetPassword(Request $request)
    // {
    //    return $this->resetPassword($request, Driver::class,"driver");
    // }
    //
    // public function driverVerify(Request $request)
    // {
    //    return $this->verifyOtp($request, Driver::class,"driver");
    // }


    public function driverLogout(Request $request)
    {
        return $this->logout($request,"driver");
    }
}
