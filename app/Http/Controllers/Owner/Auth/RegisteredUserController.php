<?php

namespace App\Http\Controllers\Owner\Auth;

use App\Http\Controllers\Controller;
use App\Models\Owner;
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
        return $this->makeStore($request ,Owner::class ,"web-owner");
    }
}
