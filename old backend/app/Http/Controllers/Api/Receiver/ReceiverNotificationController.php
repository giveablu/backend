<?php

namespace App\Http\Controllers\Api\Receiver;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\NotificationResource;

class ReceiverNotificationController extends Controller
{
    public function list(Request $request)
    {
        return NotificationResource::collection($request->user()->unreadNotifications)->additional([
            'receiver_balance' => (int)$request->user()->post->paid,
            'response' => true,
            'message' => ['Receiver Notification']
        ]);
    }

    public function remove(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'notification_id' => ['required']
        ]);

        if ($valid->fails()) {
            return response()->json(['response' => false, 'errors' => $valid->errors(), 'message' => 'Check The Fields']);
        } else {
            $notification = $request->user()->unreadNotifications->find($request->notification_id);

            if ($notification) {
                $notification->markAsRead();
                return response()->json(['response' => true, 'message' => 'Notification Removed']);
            } else {
                return response()->json(['response' => false, 'message' => 'No Notification Found']);
            }
        }
    }
}
