<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\FirebaseAuthService;
use App\Helpers\JsonResponse;
use Exception;

trait MultiAuth
{
  protected $guard = 'web';

    protected FirebaseAuthService $firebaseAuth;

    use HttpResponses;

    public function setFirebaseAuth(FirebaseAuthService $firebaseAuth): void
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * Register user (client/pharmacist/driver)
     */
    public function register($request, $modelClass, $guard)
    {
        $request->validate([
            'firebase_token' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . (new $modelClass)->getTable(),
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $firebaseUser = $this->verifyFirebaseToken($request->firebase_token);
            if (!$firebaseUser) {
                return $this->error(null, "Invalid Firebase token or missing phone number", 401);
            }

            if ($modelClass::where('firebase_uid', $firebaseUser['uid'])->exists()) {
                return $this->error(null, "This Firebase UID is already registered.", 409);
            }

            $user = $modelClass::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $firebaseUser['phone'],
                'firebase_uid' => $firebaseUser['uid'],
                'is_verified' => true,
            ]);

            $token = $user->createToken($guard . '-token', [$guard])->plainTextToken;

            return JsonResponse::respondSuccess('Account created successfully', [
                'token' => $token,
                'user' => $user,
            ]);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * Login user using Firebase token
     */
    public function login($request, $modelClass, $guard)
    {
        $request->validate([
            'firebase_token' => 'required|string',
        ]);

        try {
            $firebaseUser = $this->verifyFirebaseToken($request->firebase_token);
            if (!$firebaseUser) {
                return $this->error(null, "Invalid Firebase token or missing phone number", 401);
            }

            $user = $modelClass::where('firebase_uid', $firebaseUser['uid'])->first();
            if (!$user) {
                return $this->error(null, "User not found, please register first", 404);
            }

            $user->update(['last_login_at' => now()]);

            $token = $user->createToken($guard . '-token', [$guard])->plainTextToken;

            return JsonResponse::respondSuccess('Logged in successfully', [
                'token' => $token,
                'user' => $user,
            ]);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }

    /**
     * Logout current user by revoking token
     */
    public function logout($request, $guard)
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

    /**
     * Shared method to verify Firebase token and return user info
     */
    protected function verifyFirebaseToken(string $token): ?array
    {
        try {
            $firebaseUser = $this->firebaseAuth->verifyToken($token);

            if (!$firebaseUser || empty($firebaseUser['phone']) || empty($firebaseUser['uid'])) {
                return null;
            }

            return $firebaseUser;
        } catch (Exception $e) {
            return null;
        }
    }
}
