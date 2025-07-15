<?php

namespace App\Services;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Illuminate\Support\Facades\Log;
use Throwable;

class FirebaseAuthService
{
    protected Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Verify Firebase ID token and return UID and phone/email claims
     *
     * @param string $idToken
     * @return array{uid: string|null, phone: string|null, email: string|null, name: string|null}|null
     */
    public function verifyToken(string $idToken): ?array
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);

            return [
                'uid' => $verifiedIdToken->claims()->get('sub'),
                'phone' => $verifiedIdToken->claims()->get('phone_number'),
                'email' => $verifiedIdToken->claims()->get('email'),
                'name' => $verifiedIdToken->claims()->get('name'),
            ];
        } catch (FailedToVerifyToken $e) {
            Log::error('Firebase Token verification failed: ' . $e->getMessage());
            return null;
        } catch (Throwable $e) {
            Log::error('Unexpected error during Firebase verification: ' . $e->getMessage());
            return null;
        }
    }
}
