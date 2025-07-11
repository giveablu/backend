<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function logout(Request $request){
        $request->user()->update([
            'device_token' => null
        ]);
        $request->user()->tokens()->delete();
        return response()->json(['response' => true, 'message' => 'Logout Sucessfully'], 200);
    }
}
