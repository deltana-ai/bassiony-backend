<?php

namespace App\Traits\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

trait HandleRegistration
{

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function makeStore($request ,$modelClass ,$guard): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'address' => ['sometimes', 'string', 'max:200'],

            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . (new $modelClass)->getTable() . ',email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['sometimes', 'regex:/^[0-9]{10,15}$/', Rule::unique((new $modelClass)->getTable())],

        ]);

        $user = $modelClass::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        Auth::guard($guard)->login($user);

        return response()->noContent();
    }
}
