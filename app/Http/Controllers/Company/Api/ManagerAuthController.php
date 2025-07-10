<?php

namespace App\Http\Controllers\Company\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\MultiAuth;
use Illuminate\Support\Facades\Auth;

class ManagerAuthController extends Controller
{
    use MultiAuth;
    public function managerRegister(Request $request)
    {
       return $this->register($request, CompanyManager::class ,"manager");
    }

    public function managerLogin(Request $request)
    {
       return $this->login($request, CompanyManager::class ,"manager");
    }




    public function managerLogout(Request $request)
    {
        return $this->logout($request,"manager");
    }
}
