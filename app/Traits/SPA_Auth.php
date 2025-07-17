<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Helpers\JsonResponse;
use App\Notifications\VerifyEmailCustom;

trait SPA_Auth
{
    protected string $guard = 'web';
    protected string $modelClass = "Model";

    public function useGuard(string $guard, string $modelClass): static
    {
        $this->guard = $guard;
        $this->modelClass = $modelClass;
        return $this;
    }

    public function publicRegister(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:' . (new $this->modelClass)->getTable(),
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $this->modelClass::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user instanceof MustVerifyEmail) {
            $this->sendVerificationNotification($user);
        }

        $token = $user->createToken($this->guard . '_token')->plainTextToken;

        return JsonResponse::respondSuccess('Account created successfully', [
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function publicLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::guard($this->guard)->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.']
            ]);
        }

        $user = Auth::guard($this->guard)->user();

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return JsonResponse::respondError('Email not verified.', 403);
        }

        $token = $user->createToken($this->guard . '_token')->plainTextToken;

        return JsonResponse::respondSuccess('Login successful.', [
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function publicForgotPassword(Request $request, $broker = null)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker($broker)->sendResetLink(
            $request->only('email')
        );

        return JsonResponse::respondSuccess(__($status));
    }

    public function publicResetPassword(Request $request, $broker = null)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker($broker)->reset(
            $request->only('email', 'token', 'password', 'password_confirmation'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return JsonResponse::respondSuccess(__($status));
    }

    public function publicInvokeEmail(Request $request, $id, $hash)
    {
        $user = $this->getValidUser($id, $hash);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return JsonResponse::respondSuccess("Email verified successfully.");
    }

    public function publicResentEmail(Request $request)
    {
        $user = auth($this->guard)->user();

        if (!$user) {
            return JsonResponse::respondError("Unauthenticated", 401);
        }

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            $this->sendVerificationNotification($user);
            return JsonResponse::respondSuccess("Verification email has been sent.");
        }

        return JsonResponse::respondError("Email already verified.", 400);
    }

    protected function sendVerificationNotification(MustVerifyEmail $user): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify.' . $this->guard,
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $user->notify(new VerifyEmailCustom($verificationUrl));
    }

    public function publicLogout(Request $request)
    {
        $user = auth($this->guard)->user();

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

        return JsonResponse::respondSuccess('Logged out successfully.');
    }

    protected function getValidUser($id, $hash)
    {
        $user = $this->modelClass::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        return $user;
    }
}
