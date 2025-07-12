<?php

namespace App\Traits\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

trait HasVerifyEmail
{


    /**
     * Mark the authenticated user's email address as verified.
     */
    public function makeInvoke( $request ,$guard , $redirectTo): RedirectResponse
    {
         $user = auth($guard)->user();
         if (! $user) {
             return response()->JsonResponse('Unauthorized.',403);
         }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url').$redirectTo.'?verified=1'
            );
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
          event(new Verified($user));
        }

        return redirect()->intended(
            config('app.frontend_url'). $redirectTo.'?verified=1'
        );
    }
}
