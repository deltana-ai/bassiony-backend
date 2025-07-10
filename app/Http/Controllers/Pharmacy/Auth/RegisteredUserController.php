<?php

namespace App\Http\Controllers\Pharmacy\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pharmacist;
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
        return $this->makeStore($request ,Pharmacist::class ,"web-pharmacist");
    }
}
