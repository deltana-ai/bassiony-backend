<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGuardMatchesToken
{
    public function handle(Request $request, Closure $next, $guardModel)
    {
        $user = auth()->user();

        if (! $user || ! ($user instanceof $guardModel)) {
            return response()->json(['message' => 'Unauthorized - guard mismatch'], 401);
        }

        return $next($request);
    }
}
