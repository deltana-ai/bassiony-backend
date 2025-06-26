<?php

namespace App\Traits;

use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
trait OtpAuthenticates
{
    public function generateAndSendOtp($email, $name,$guard)
    {
        $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $otpHashed = Hash::make($otp);
        Otp::updateOrCreate(
           ['email' => $email, 'guard' => $guard],
            [
              'otp' => $otpHashed,
              'expires_at' => Carbon::now()->addMinutes(5),
            ]
        );
        $message = __('auth.welcome', ['name' => $name, 'otp' => $otp]);
        // Mail::raw($message, function ($msg) use ($email) {
        //     $msg->to($email)->subject(__("auth.OTP Verification"));
        // });
        Log::info("Hello $name, your OTP is: $otp");

    }

    public function checkOtp($email, $otp ,$guard)
    {
        $otpRecord = Otp::where('email', $email)->where('guard', $guard)->first();


        if (!$otpRecord || $otpRecord->expires_at->isPast()) {
            return ['success' => false, 'message' => __('auth.OTP expired or not found ')];
        }

        if (!Hash::check($otp, $otpRecord->otp)) {
            return ['success' => false, 'message' => __('auth.Invalid OTP')];
        }

        $otpRecord->delete();

        return ['success' => true];
    }
}
