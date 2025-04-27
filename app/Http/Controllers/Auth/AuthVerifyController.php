<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthVerifyController extends Controller
{
    public function verificationNotice(){
        return view('pages.auth.resend');
    }

    public function verificationVerify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect()->route('admin.dashboard');
    }

    public function verifyPage(){
        return view('pages.auth.resend');
    }

    public function verificationResend(Request $request){
        $user = $request->user();

        if (!$user->email_verified_at) {
            if (RateLimiter::tooManyAttempts('verifymail-resend:' . $user->id, 5)) {
                $available = RateLimiter::availableIn('verifymail-resend:' . $user->id);
                return response()->json(['message' => 'Too many mail sent, try again after ' . $available . ' seconds']);
            }

            RateLimiter::hit('verifymail-resend:' . $user->id);
            $request->user()->sendEmailVerificationNotification();
            $remaining = RateLimiter::remaining('verifymail-resend:' . $user->id, 5);
            return response()->json(['message' => 'Verification Mail Sent, ' . $remaining . ' remains']);
        } else {
            return response()->json(['message' => 'Email Already Verified', 'url' => route('user.dashboard')]);
        }
    }
}
