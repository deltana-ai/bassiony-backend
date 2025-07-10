<?php

namespace App\Http\Controllers\Owner\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use App\Models\CompanyManager;
use App\Traits\Auth\HandleAuthentication;

class AuthenticatedSessionController extends Controller
{
  use HandleAuthentication;

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
       return $this->makeStore( $request ,"web-owner");
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        return $this->makeDestroy( $request ,"web-owner");
    }
}
