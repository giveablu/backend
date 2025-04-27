<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function deleteAccount(Request $request, $id)
    {
        $user = User::find($id);
       

        if ($user) {
            if ($user->id == $request->user()->id) {

                $user->status = 'I';
                $user->save();
                return response()->json(['response' => true, 'status' => 1, 'message' => ['Account Deleted']]);
            } else {
                return response()->json(['response' => false, 'status' => 0, 'message' => ['Cannot Delete This Account']]);
            }
        } else {
            return response()->json(['response' => false, 'status' => 0, 'message' => ['No Account Found']]);
        }
    }
}
