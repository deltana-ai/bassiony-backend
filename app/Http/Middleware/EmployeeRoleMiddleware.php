<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next , string $role): Response
    {
        $employee = auth('employees')->user();

        if (!$employee || !$employee->hasRole($role) || !$employee->active==1) {
            abort(403, 'Unauthorized for this role.');
        }

        return $next($request);
    }
}
