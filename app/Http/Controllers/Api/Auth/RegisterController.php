<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Otp;
use App\Models\User;
use App\Mail\EmailVerify;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Debug: Log what data is being received
        \Log::info('Registration request received:', $request->all());
        
        $validData = Validator::make($request->all(), [
            'name' => 'required',
            'email' => ['required', 'email', 'unique:users'],
            'phone' => ['required', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(['donor', 'receiver'])]
        ], [
            'password.confirmed' => 'Password do not match',
            'role.required' => 'Role is required',
            'role.in' => 'Role must be either donor or receiver'
        ]);

        if ($validData->fails()) {
            // Debug: Log validation errors
            \Log::error('Registration validation failed:', $validData->errors()->toArray());
            
            $message = collect([
                $validData->errors()->first('name'),
                $validData->errors()->first('email'),
                $validData->errors()->first('phone'),
                $validData->errors()->first('password'),
                $validData->errors()->first('role')
            ])->filter(fn($item) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            $user  = User::where('email', $request->email)->orWhere('phone', $request->phone)->first();

            if ($user) {
                if (($user->role == 'receiver') || ($user->role == 'admin')) {
                    return response()->json(['response' => false, 'message' => ['You have already registered']]);
                }
            } else {
                $newuser = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'role' => $request->role,
                    'password' => Hash::make($request->password),
                    'search_id' => Str::random(10),
                    'joined_date' => now(),
                ]);

                $data = Otp::updateOrCreate([
                    'user_id' => $newuser->id,
                ], [
                    'phone' => $newuser->phone,
                    'email' => $newuser->email,
                    'otp' => random_int(100000, 999999),
                    'expire' => now()->addMinutes(5)
                ]);

                Mail::to($request->email)->send(new EmailVerify($data));

                return response()->json(['data' => ['email' => $request->email], 'response' => true, 'message' => ['OTP sent successfully']]);
            }
        }
    }
}
