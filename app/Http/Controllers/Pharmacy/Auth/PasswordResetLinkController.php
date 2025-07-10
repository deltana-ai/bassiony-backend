<?php

namespace App\Http\Controllers\Pharmacy\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\Auth\HandlePasswordReset;

class PasswordResetLinkController extends Controller
{
    use HandlePasswordReset;
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
      return $this->makeStore( $request ,"web_pharmacists");
    }
}
