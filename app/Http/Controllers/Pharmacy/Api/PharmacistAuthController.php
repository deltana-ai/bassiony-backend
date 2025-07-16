<?php

namespace App\Http\Controllers\Pharmacy\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pharmacist;

use App\Traits\MultiAuth;
use App\Services\FirebaseAuthService;

class PharmacistAuthController extends Controller
{
    use MultiAuth;
    public function __construct(FirebaseAuthService $firebaseAuth)
    {
        $this->setFirebaseAuth($firebaseAuth);
        $this->guard = 'pharmacist';

    }
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
