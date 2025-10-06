<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Log;

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
            ], 422);
        }

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'response' => false,
                'message' => ['Unauthenticated.'],
            ], 401);
        }

        $requestedRole = $request->string('role')->trim();
        $currentRole = is_string($user->role) ? trim($user->role) : '';

        if (strcasecmp($currentRole, $requestedRole) === 0) {
            return (new UserResource($user))->additional([
                'response' => true,
                'message' => ["You're already using the {$requestedRole} experience."],
            ]);
        }

        $previousRole = $currentRole;
        $user->role = $requestedRole;
        $user->save();
        $user->refresh();

        Log::info('User role switched', [
            'user_id' => $user->id,
            'from' => $previousRole ?: null,
            'to' => $requestedRole,
        ]);

        return (new UserResource($user))->additional([
            'response' => true,
            'message' => ["Role updated successfully. You're now in {$requestedRole} mode."],
        ]);
    }

    // Add other general auth methods here if needed
    // But NOT forgot password stuff!
}
