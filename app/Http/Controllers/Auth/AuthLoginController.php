<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthLoginController extends Controller
{
    public function loginPage()
    {
        return view('pages.auth.login');
    }

    public function loginPost(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'exists:users,email'],
            'password' => ['required'],
        ], [
            'email.exists' => 'Check Your Email Please'
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return response()->json(['success' => 'Login Successful,  redirecting...', 'url' => route('admin.dashboard')]);
        } else {
            return response()->json(['error' => 'Given Credentials are Incorrct']);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
