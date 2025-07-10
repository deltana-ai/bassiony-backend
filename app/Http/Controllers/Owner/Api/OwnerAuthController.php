<?php

namespace App\Http\Controllers\Owner\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\MultiAuth;
use Illuminate\Support\Facades\Auth;

class OwnerAuthController extends Controller
{
    use MultiAuth;
    public function adminRegister(Request $request)
    {
       return $this->register($request, Owner::class ,"owner");
    }

    public function adminLogin(Request $request)
    {
       return $this->login($request, Owner::class ,"owner");
    }




    public function adminLogout(Request $request)
    {
        return $this->logout($request,"admin");
    }
}
