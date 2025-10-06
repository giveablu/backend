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

            $grossAmount = (float) $request->tobepaid;

            if ($grossAmount >= $default_amount) {
                $post = Post::where('id', $request->donation_id)->first();
                if ($post) {
                    [$processingFee, $platformFee, $netAmount] = $this->calculateSplit($grossAmount);

                    $request->user()->donations()->attach($request->donation_id, [
                        'gross_amount' => $grossAmount,
                        'processing_fee' => $processingFee,
                        'platform_fee' => $platformFee,
                        'net_amount' => $netAmount,
                        'currency' => $this->resolveCurrency(),
                        'processor_payload' => null,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $post->update([
                        'paid' => (float) $post->paid + $netAmount
                    ]);

                    // notification list
                    $data = [
                        'to' => 'receiver',
                        'title' => 'New Donation Received',
                        'image' => $this->userPhoto($request->user()->photo),
                        'description' => $post->biography,
                        'date' => Carbon::now(),
                        'amount' => $netAmount,
                        'metadata' => [
                            'gross_amount' => $grossAmount,
                            'processing_fee' => $processingFee,
                            'platform_fee' => $platformFee,
                        ],
                    ];

                    $user = User::find($post->user);
                    if ($user) {
                        Notification::send($user, new UserNotification($data));
                    }

                    // push notification
                    $title = 'New Donation Received';
                    $body = sprintf(
                        'You received $%0.2f (after $%0.2f in fees).',
                        $netAmount,
                        $processingFee + $platformFee
                    );
                    $pageName = "ReceiverBalanceScreen";

                    $tokens = $user->whereNotNull('device_token')->pluck('device_token')->toArray();
                    Notification::send(null, new PushNotification($title, $body, $pageName, $tokens));

                    return response()->json(['response' => true, 'message' => ['Payment recorded successfully.']]);
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

    private function calculateSplit(float $gross): array
    {
        $processingFee = round($gross * 0.03, 2);
        $platformFee = round($gross * 0.02, 2);
        $netAmount = round($gross - $processingFee - $platformFee, 2);

        // Handle rounding discrepancies by adjusting platform fee
        $difference = round($gross - ($processingFee + $platformFee + $netAmount), 2);
        if ($difference !== 0.0) {
            $platformFee = round($platformFee + $difference, 2);
        }

        return [$processingFee, $platformFee, $netAmount];
    }

    private function resolveCurrency(): string
    {
        return config('app.currency', 'USD');
    }
}
