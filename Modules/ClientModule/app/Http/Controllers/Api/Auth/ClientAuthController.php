<?php

namespace Modules\ClientModule\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\MultiAuth;

class ClientAuthController extends Controller
{

    use MultiAuth;

    public function clientRegister(Request $request)
    {
      return  $this->register($request, User::class ,"client");
    }

    public function clientLogin(Request $request)
    {
       return $this->login($request, User::class ,"client");
    }


    public function clientLogout(Request $request)
    {

        return $this->logout($request ,"client");
    }
}
