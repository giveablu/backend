<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    public function saveToken(Request $request)
    {

        dd($request);
        $request->validate([
            'device_token' => 'required|string',
        ]);

        $user = Auth::user();
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json(['message' => 'Device token saved successfully']);
    }
}
