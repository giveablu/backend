<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Otp;
use App\Models\User;
use App\Mail\EmailVerify;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Notifications\NewUserRegistered;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Debug: Log what data is being received (avoid storing raw passwords)
        \Log::info('Registration request received:', [
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role' => $request->input('role'),
        ]);

        $existingUser = User::where('email', $request->input('email'))
            ->orWhere('phone', $request->input('phone'))
            ->first();

        $validData = Validator::make($request->all(), [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users')
                    ->whereNotNull('email_verified_at')
                    ->ignore($existingUser?->id),
            ],
            'phone' => [
                'required',
                Rule::unique('users')
                    ->whereNotNull('phone_verified_at')
                    ->ignore($existingUser?->id),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(['donor', 'receiver'])],
        ], [
            'password.confirmed' => 'Password do not match',
            'role.required' => 'Role is required',
            'role.in' => 'Role must be either donor or receiver',
            'email.unique' => 'This email is already associated with a verified account.',
            'phone.unique' => 'This phone number is already associated with a verified account.',
        ]);

        if ($validData->fails()) {
            // Debug: Log validation errors
            \Log::error('Registration validation failed:', $validData->errors()->toArray());

            $message = collect([
                $validData->errors()->first('name'),
                $validData->errors()->first('email'),
                $validData->errors()->first('phone'),
                $validData->errors()->first('password'),
                $validData->errors()->first('role'),
            ])->filter(fn ($item) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        }

        if ($existingUser && ($existingUser->email_verified_at || $existingUser->phone_verified_at || $existingUser->role === 'admin')) {
            return response()->json([
                'response' => false,
                'message' => ['You have already registered. Please sign in or reset your password.'],
            ]);
        }

        try {
            [$user, $otp] = DB::transaction(function () use ($existingUser, $request) {
                $targetUser = $existingUser ?? new User();

                $targetUser->forceFill([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'role' => $request->role,
                    'password' => Hash::make($request->password),
                    'search_id' => $targetUser->search_id ?? Str::random(10),
                    'joined_date' => $targetUser->joined_date ?? now(),
                    'email_verified_at' => null,
                    'phone_verified_at' => null,
                ])->save();

                $otpData = Otp::updateOrCreate([
                    'user_id' => $targetUser->id,
                ], [
                    'phone' => $targetUser->phone,
                    'email' => $targetUser->email,
                    'otp' => (string) random_int(100000, 999999),
                    'expire' => now()->addMinutes(5),
                ]);

                return [$targetUser->fresh(), $otpData];
            });
        } catch (\Throwable $exception) {
            \Log::error('Registration transaction failed', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'response' => false,
                'message' => ['We could not complete your registration. Please try again shortly.'],
            ], 500);
        }

        try {
            if ($user->email) {
                Mail::to($user->email)->send(new EmailVerify($otp));
            }
        } catch (\Throwable $exception) {
            \Log::error('Registration OTP dispatch failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'response' => false,
                'message' => ['We saved your registration but could not send the verification email. Please try again once email service is restored.'],
            ], 500);
        }

        try {
            $admins = User::query()
                ->where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewUserRegistered($user));
            }
        } catch (\Throwable $exception) {
            \Log::warning('Failed to notify admins of new registration', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'data' => ['email' => $user->email],
            'response' => true,
            'message' => ['OTP sent successfully'],
        ]);
    }
}
