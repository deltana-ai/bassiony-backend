<?php

namespace App\Http\Controllers\Client\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\MultiAuth;
use App\Services\FirebaseAuthService;

class ClientAuthController extends Controller
{

    use MultiAuth;
    public function __construct(FirebaseAuthService $firebaseAuth)
    {
        $this->setFirebaseAuth($firebaseAuth);
        $this->guard = 'client';

    }

    public function clientRegister(Request $request)
    {
      return  $this->register($request, User::class ,$this->guard);
    }

    public function clientLogin(Request $request)
    {
       return $this->login($request, User::class ,$this->guard);
    }


    public function clientLogout(Request $request)
    {

        return $this->logout($request ,$this->guard);
    }
}
