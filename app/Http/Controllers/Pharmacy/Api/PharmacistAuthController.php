<?php

namespace App\Http\Controllers\Pharmacy\Api;

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




    public function pharmacistLogout(Request $request)
    {

        return $this->logout($request ,"pharmacist");
    }
}
