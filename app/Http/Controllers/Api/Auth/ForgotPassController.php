<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use Log;

class ForgotPassController extends Controller
{
    public function forgotPass(Request $request)
    {
        Log::info('forgot-password request', $request->all());

        if ($request->has('authvalue') && !$request->has('email')) {
            $request->merge(['email' => $request->input('authvalue')]);
        }

        $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'response' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $otp = rand(100000, 999999);

        Otp::updateOrCreate(
            ['email' => $user->email],
            [
                'otp' => $otp,
                'phone' => 'N/A',
                'user_id' => $user->id
            ]
        );

        return response()->json([
            'response' => true,
            'data' => [
                'message' => 'OTP sent successfully.',
                'otp' => $otp,
                'code' => $otp,
                'token' => $otp,
                'faqs' => [
                    [
                        'question' => 'What is Blu?',
                        'answer' => 'Blu is a peer-to-peer donation platform.'
                    ]
                ]
            ]
        ]);
    }
}
