<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // This controller should ONLY handle general auth stuff
    // NO forgot password methods here!
    
    public function test()
    {
        return response()->json([
            'message' => 'Clean AuthController - no forgot password methods',
            'controller' => 'AuthController',
            'file' => __FILE__,
            'methods' => [
                'test' => 'Just for testing'
            ]
        ]);
    }
    
    public function switchRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:donor,receiver',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'message' => $validator->errors()->all(),
            ], 400);
        }

        $user = $request->user();
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'response' => true,
            'message' => ['Role updated successfully.'],
            'data' => $user
        ]);
    }

    // Add other general auth methods here if needed
    // But NOT forgot password stuff!
}
