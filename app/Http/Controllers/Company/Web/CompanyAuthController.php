<?php
namespace App\Http\Controllers\Company\Web;

use App\Http\Controllers\Controller;
use App\Traits\SPA_Auth;
use App\Models\CompanyManager;
use Illuminate\Http\Request;
class CompanyAuthController extends Controller
{
    use SPA_Auth;
    public function __construct()
    {
      $this->guard = 'web-manager';
    }

    public function register(Request $request)
    {
        return $this->publicRegister($request, CompanyManager::class);
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
        return $this->publicInvokeEmail( $request, $id, $hash,CompanyManager::class);
    }

    public function logout(Request $request)
    {

        return $this->publicLogout($request ,$this->guard);
    }


}
