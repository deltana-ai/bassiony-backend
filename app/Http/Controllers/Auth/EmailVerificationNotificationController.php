<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{

    protected string $guard = 'web';
    protected string $redirectTo = '/dashboard';
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user($this->guard)->hasVerifiedEmail()) {
            return redirect()->intended($this->redirectTo);
        }

        $request->user($this->guard)->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
