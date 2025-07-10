<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AllEmailVerificationRequest extends EmailVerificationRequest
{
    public function user($guard = null)
    {
        return parent::user('web-manager');
    }
}
