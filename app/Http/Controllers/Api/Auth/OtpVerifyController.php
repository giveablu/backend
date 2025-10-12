<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Otp;
use App\Models\User;
use App\Mail\AppPassReset;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OtpVerifyController extends Controller
{
    public function verifyOtp(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required',
            'otp' => 'required',
            'device_token' => 'nullable',
        ]);

        if ($valid->fails()) {

            $message = collect([
                $valid->errors()->first('phone'),
                $valid->errors()->first('otp'),
                $valid->errors()->first('device_token'),
            ])->filter(fn($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            $found = Otp::where('email', $request->email)->where('otp', $request->otp)->first();

            if ($found) {
                if (Carbon::now() > $found->expire) {
                    return response()->json(['response' => false, 'message' => ['Time Expired']]);
                } else {
                    $user =  User::where('id', $found->user_id)->first();

                    $user->update([
                        'email_verified_at' => Carbon::now()
                    ]);

                    $found->delete();

                    if ($user->tokens()->count()) {
                        $user->tokens()->delete();
                    }
                    $token = $user->createToken('app-token')->plainTextToken;

                    $user->update([
                        'device_token' => $request->device_token,
                        'last_login_at' => now(),
                    ]);

                    return (new UserResource($user))->additional([
                        'response' => true,
                        'meta' => [
                            'access_token' => $token
                        ],
                        'message' => ['OTP Successfully Verified']
                    ]);
                }
            } else {
                return response()->json(['response' => false, 'message' => ['OTP is invalid']]);
            }
        }
    }

    public function resendOtp(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($valid->fails()) {
            $message = [
                $valid->errors()->first('email'),
            ];
            return response()->json(['response' => false, 'message' => $message]);
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) {

                $email = $user->email;
                $code = rand(10000, 99999);
                $expire = now()->addMinutes(5);

                Mail::to($user->email)->send(new AppPassReset($code, $email, $expire));
                Otp::updateOrCreate([
                    'user_id' => $user->id
                ], [
                    'email' => $email,
                    'otp' => $code,
                    'expire' => $expire
                ]);

                return response()->json(['data' => ['email' => $email], 'response' => true, 'message' => ['OTP Resent Successfully']]);
            } else {
                return response()->json(['response' => false, 'message' => ['User not found']]);
            }
        }
    }
}
