<?php

namespace App\Http\Controllers\Api\Donor;

use App\Models\Post;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Notifications\PushNotification;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class DonorPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['user.role:donor']);
    }

    public function success(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'donation_id' => 'required',
            'tobepaid' => 'required|max:10',
        ]);

        if ($valid->fails()) {
            $message = collect([
                $valid->errors()->first('donation_id'),
                $valid->errors()->first('tobepaid'),
            ])->filter(fn ($item, $key) => !empty($item))->values();

            return response()->json(['response' => false, 'message' => $message]);
        } else {
            $default_amount = Setting::select('default_amount')->first()->default_amount;

            if ($request->tobepaid >= $default_amount) {
                $post = Post::where('id', $request->donation_id)->first();
                if ($post) {
                    // Update donation table
                    $request->user()->donations()->attach($request->donation_id, [
                        'paid_amount' => $request->tobepaid,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    // update receiver balance
                    $post->update([
                        'paid' => (int)$post->paid + (int)$request->tobepaid
                    ]);

                    // notification list
                    $data = [
                        'to' => 'receiver',
                        'title' => 'New Donation Received',
                        'image' => $this->userPhoto($request->user()->photo),
                        'description' => $post->biography,
                        'date' => Carbon::now(),
                        'amount' => $request->tobepaid
                    ];

                    $user = User::find($post->user);
                    if ($user) {
                        Notification::send($user, new UserNotification($data));
                    }

                    // push notification
                    $title = 'New Donation Received';
                    $body = 'you have got a payment of $' . $request->tobepaid;
                    $pageName = "ReceiverBalanceScreen";

                    $tokens = $user->whereNotNull('device_token')->pluck('device_token')->toArray();
                    Notification::send(null, new PushNotification($title, $body, $pageName, $tokens));

                    return response()->json(['response' => true, 'message' => ['payment Done']]);
                } else {
                    return response()->json(['response' => false, 'message' => ['Post not found']]);
                }
            } else {
                return response()->json(['response' => false, 'message' => ['Amount Not Acceptable']]);
            }
        }
    }

    public function userPhoto($photo)
    {
        if (!is_null($photo)) {
            return URL::to('/storage') . '/' . $photo;
        } else {
            return null;
        }
    }
}
