<?php

namespace App\Http\Controllers\Delivery\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;

use App\Traits\MultiAuth;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseAuthService;

class DriverAuthController extends Controller
{
    use MultiAuth;
    public function __construct(FirebaseAuthService $firebaseAuth)
    {
        $this->setFirebaseAuth($firebaseAuth);
    }
    public function driverRegister(Request $request)
    {
       return $this->register($request, Driver::class ,"driver");
    }

    public function driverLogin(Request $request)
    {
       return $this->login($request, Driver::class ,"driver");
    }




    public function driverLogout(Request $request)
    {
        return $this->logout($request,"driver");
    }
}
