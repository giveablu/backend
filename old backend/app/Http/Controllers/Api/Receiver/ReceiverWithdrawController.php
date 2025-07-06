<?php

namespace App\Http\Controllers\Api\Receiver;

use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class ReceiverWithdrawController extends Controller
{
    public function __construct()
    {
        $this->middleware(['user.role:receiver']);
    }

    public function create(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'amount' => 'required',
        ]);

        if ($valid->fails()) {
            $message = collect([
                $valid->errors()->first('amount'),
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {

            $post = $request->user()->post;
            if ((int)$request->amount > 0 && (int)$request->amount <= $post->paid) {

                if (!is_null($request->user()->bankDetail)) {

                    Withdraw::create([
                        'user_id' => $request->user()->id,
                        'batch_id' => $request->batch_id,
                        'batch_status' => $request->batch_status,
                        'paypal_id' => $request->paypal_id,
                        'amount' => $request->amount,
                        'status' => 0
                    ]);

                    $post->update([
                        'paid' => (int)$post->paid - (int)$request->amount
                    ]);

                    return response()->json(['data' => ['available' => (int)$post->paid], 'response' => true, 'message' => ['Request Sent']]);
                } else {
                    return response()->json(['response' => false, 'message' => ['Update Bank Details']]);
                }
            } else {
                return response()->json(['response' => false, 'message' => ['You cannot withdraw this amount']]);
            }
        }
    }
}
