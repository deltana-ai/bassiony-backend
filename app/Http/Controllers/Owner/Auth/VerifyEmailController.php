<?php

namespace App\Http\Controllers\Owner\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Traits\Auth\HasVerifyEmail;
use Illuminate\Http\Request;
use App\Http\Requests\AllEmailVerificationRequest;

class VerifyEmailController extends Controller
{
    use HasVerifyEmail;
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(AllEmailVerificationRequest $request)
    {
        return $this->makeInvoke( $request ,"web-owner" , "/owner/dashboard");
    }
}
