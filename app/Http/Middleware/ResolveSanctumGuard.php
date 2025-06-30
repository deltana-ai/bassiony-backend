<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ResolveSanctumGuard
{

  public function handle(Request $request, Closure $next): Response
{
  $header = $request->bearerToken();

  if (!$header) {
      return response()->json(['message' => 'No token provided'], 401);
  }

  $accessToken = PersonalAccessToken::findToken($header);

  if (!$accessToken) {
      return response()->json(['message' => 'Invalid token'], 401);
  }

  $model = $accessToken->tokenable;
  $resolvedGuard = null;

  if ($model instanceof \App\Models\User) {
      $resolvedGuard = 'client';
  } elseif ($model instanceof \App\Models\Pharmacist) {
      $resolvedGuard = 'pharmacist';
  } elseif ($model instanceof \App\Models\Driver) {
      $resolvedGuard = 'driver';
  }

  if ($resolvedGuard) {
      Auth::guard($resolvedGuard)->setUser($model);
      app()->instance('auth', Auth::guard($resolvedGuard));
  }

  return $next($request);
}

}
