<?php

namespace App\Http\Controllers\Owner\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{

  protected string $guard = 'web';
  protected string $redirectTo = '/dashboard';
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
       $user = auth($this->guard)->user();
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url').$this->redirectTo.'?verified=1'
            );
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($request->user($this->guard)));
        }

        return redirect()->intended(
            config('app.frontend_url'). $this->redirectTo.'?verified=1'
        );
    }
}
