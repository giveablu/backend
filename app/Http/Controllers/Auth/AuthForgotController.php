<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as Custom;
use Illuminate\Auth\Events\PasswordReset;

class AuthForgotController extends Controller
{
    public function forgotPage()
    {
        return view('pages.auth.forgot');
    }

    public function forgotPost(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json(['status' => $status]);
    }

    public function resetPage($token)
    {
        return view('pages.auth.reset', [
            'token' => $token
        ]);
    }

    public function resetPost(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Custom::defaults()]
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json(['status' => $status, 'url' => route('login')]);
    }
}
