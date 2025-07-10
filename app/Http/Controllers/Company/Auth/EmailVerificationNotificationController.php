<?php

namespace App\Http\Controllers\Company\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\Auth\HandleEmailVerification;

class EmailVerificationNotificationController extends Controller
{

   use HandleEmailVerification;
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request)
    {
        return $this->makeStore( $request ,"web-manager" , "/company/dashboard");
    }
}
