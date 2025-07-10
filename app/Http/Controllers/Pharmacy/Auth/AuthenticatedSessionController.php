<?php

namespace App\Http\Controllers\Pharmacy\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use App\Models\Pharmacist;
use App\Traits\Auth\HandleAuthentication;

class AuthenticatedSessionController extends Controller
{
  use HandleAuthentication;

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
       return $this->makeStore( $request ,"web-pharmacist");
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        return $this->makeDestroy( $request ,"web-pharmacist");
    }
}
