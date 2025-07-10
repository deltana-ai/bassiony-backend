<?php

namespace App\Traits\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HandleEmailVerification
{


    /**
     * Send a new email verification notification.
     */
    public function makeStore( $request ,$guard , $redirectTo): JsonResponse|RedirectResponse
    {
        if ($request->user($guard)->hasVerifiedEmail()) {
            return redirect()->intended($redirectTo);
        }

        $request->user($guard)->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
