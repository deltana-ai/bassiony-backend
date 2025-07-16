<?php

namespace App\Http\Controllers\Owner\Web;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Traits\SPA_Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class OwnerAuthController extends Controller
{
  use SPA_Auth;
  public function __construct()
  {
    $this->guard = 'web-owner';
  }

  public function register(Request $request)
  {
      return $this->publicRegister($request, Owner::class);
  }

  public function login(Request $request)
  {
      return $this->publicLogin($request);
  }

  public function forgotPassword(Request $request)
  {
      return $this->publicForgotPassword($request);
  }

  public function resetPassword(Request $request)
  {
      return $this->publicResetPassword($request);
  }

  public function invokeEmail(Request $request , $id, $hash)
  {
      return $this->publicInvokeEmail( $request, $id, $hash,Owner::class);
  }

  public function logout(Request $request)
  {

      return $this->publicLogout($request ,$this->guard);
  }


}
