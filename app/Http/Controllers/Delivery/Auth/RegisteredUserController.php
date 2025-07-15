<?php

namespace App\Http\Controllers\Company\Auth;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Traits\Auth\HandleRegistration;

class RegisteredUserController extends Controller
{
    use HandleRegistration;
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        return $this->makeStore($request ,Driver::class ,"web-driver");
    }
}
