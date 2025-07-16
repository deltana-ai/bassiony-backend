<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\Rules;
use Laravel\Sanctum\PersonalAccessToken;
use App\Helpers\JsonResponse;
use Illuminate\Auth\Events\Verified;

use Exception;

trait SPA_Auth
{

    protected $guard = 'web';

    /**
     * register
     */
    public function publicRegister($request, $modelClass)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . (new $modelClass)->getTable(),
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $modelClass::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user instanceof MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }
        $token = $user->createToken($this->guard . '_token')->plainTextToken;


        return JsonResponse::respondSuccess('Account created successfully', [
            'token' => $token,
            'user' => $user,
        ]);


    }

    /**
     * login
     */
    public function publicLogin($request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::guard($this->guard)->attempt($credentials)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $user = Auth::guard($this->guard)->user();

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
          return JsonResponse::respondError('Email not verified.',403);
        }

        $token = $user->createToken($this->guard . '_token')->plainTextToken;


        return JsonResponse::respondSuccess('Login successful.', [
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     *password forgot
     */
    public function publicForgotPassword($request, $broker = null)
    {
        $status = Password::broker($broker)->sendResetLink(
            $request->only('email')
        );
        return JsonResponse::respondSuccess(__($status));
    }

    /**
     * password reset
     */
    public function publicResetPassword($request, $broker = null)
    {
        $status = Password::broker($broker)->reset(
            $request->only('email', 'token', 'password', 'password_confirmation'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return JsonResponse::respondSuccess(__($status));
    }

    public function publicInvokeEmail( $request, $id, $hash,$modelClass)
    {
        $user = $this->getValidUser($id, $hash ,$modelClass);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
        return JsonResponse::respondSuccess("Email verified successfully.");

    }

    protected function getValidUser($id, $hash,$modelClass)
    {
        $user = $modelClass::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        return $user;
    }


    public function publicLogout($request, $guard)
    {
        $user = auth($guard)->user();
        if (!$user) {
            return JsonResponse::respondError("Unauthenticated", 401);
        }

        $token = $request->bearerToken();
        if (!$token) {
            return JsonResponse::respondError("No token provided", 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (
            !$accessToken ||
            $accessToken->tokenable_id !== $user->id ||
            $accessToken->tokenable_type !== get_class($user)
        ) {
            return JsonResponse::respondError("Invalid token", 401);
        }

        $accessToken->delete();

        return JsonResponse::respondSuccess('Logged out successfully');
    }

}
