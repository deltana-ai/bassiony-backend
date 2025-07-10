<?php

namespace App\Traits\Auth;

//use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

trait HandleAuthentication
{


    /**
     * Handle an incoming authentication request.
     */
    public function makeStore( $request ,$guard): Response
    {
        $request->authenticate($guard);

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function makeDestroy( $request ,$guard): Response
    {
        Auth::guard($guard)->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
