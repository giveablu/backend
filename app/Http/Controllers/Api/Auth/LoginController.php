<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validData = Validator::make($request->all(), [
            'authvalue' => 'required',
            'password' => 'required',
            'device_token' => 'nullable',
        ]);

        if ($validData->fails()) {
            $message = collect([
                $validData->errors()->first('authvalue'),
                $validData->errors()->first('password'),
                $validData->errors()->first('device_token')
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            $user = User::where('email', $request->authvalue)->first();

            if ($user) {
                $inputPassword = trim($request->input('password'));
                if (!Hash::check($inputPassword, $user->password)) {
                    return response()->json([
                        'response' => false,
                        'message' => ['Provided credentials are incorrect']
                    ]);
                } else {
                    // Check if email is verified (but don't block login)
                    if (!$user->email_verified_at) {
                        return response()->json([
                            'response' => false,
                            'message' => ['Please verify your email address first. Check your email for verification code.']
                        ]);
                    }

                    if ($user->tokens()->count()) {
                        $user->tokens()->delete();
                    }

                    $token = $user->createToken('app-token')->plainTextToken;

                    $user->update([
                        'device_token' => $request->device_token
                    ]);

                    return (new UserResource($user))->additional([
                        'response' => true,
                        'meta' => [
                            'access_token' => $token
                        ],
                        'message' => ['Logged-in Successfully']
                    ]);
                }
            } else {
                return response()->json(['response' => false, 'message' => ['Please check your credentials']]);
            }
        }
    }
}
