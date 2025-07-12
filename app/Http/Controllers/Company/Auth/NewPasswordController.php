<?php

namespace App\Http\Controllers\Company\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use App\Traits\Auth\HandleNewPassword;

class NewPasswordController extends Controller
{
    use HandleNewPassword;
    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
      return $this->makeStore( $request ,"web_manager");
    }
}
