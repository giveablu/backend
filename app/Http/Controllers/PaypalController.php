<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PushNotification;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class PaypalController extends Controller
{
    public function captureOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderID' => ['required', 'string'],
            'post_id' => ['required', 'exists:posts,id'],
            'donor_id' => ['required', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'message' => $validator->errors()->all(),
            ], 422);
        }

        $orderId = $validator->validated()['orderID'];

        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post(env('PAYPAL_API_BASE') . "/v2/checkout/orders/{$orderId}/capture");

        if ($response->failed()) {
            $payload = $response->json();
            Log::error('paypal.capture.failed', ['order_id' => $orderId, 'payload' => $payload]);

            return response()->json([
                'response' => false,
                'message' => ['Failed to capture order.'],
                'details' => $payload,
            ], 500);
        }

        $payload = $response->json();
        Log::info('paypal.capture.success', ['order_id' => $orderId]);

        $purchaseUnit = data_get($payload, 'purchase_units.0.payments.captures.0');

        if (!$purchaseUnit) {
            Log::warning('paypal.capture.missing_capture', ['order_id' => $orderId, 'payload' => $payload]);

            return response()->json([
                'response' => false,
                'message' => ['Unable to locate captured amount from PayPal response.'],
            ], 500);
        }

        $grossAmount = (float) data_get($purchaseUnit, 'amount.value', 0);
        $currency = strtoupper((string) data_get($purchaseUnit, 'amount.currency_code', $this->resolveCurrency()));

        if ($grossAmount <= 0) {
            Log::warning('paypal.capture.invalid_amount', ['order_id' => $orderId, 'payload' => $purchaseUnit]);

            return response()->json([
                'response' => false,
                'message' => ['Invalid donation amount received from PayPal.'],
            ], 422);
        }

        [$processingFee, $platformFee, $netAmount] = $this->calculateSplit($grossAmount);

        $post = Post::findOrFail($validator->validated()['post_id']);
        $donor = User::findOrFail($validator->validated()['donor_id']);

        $donation = Donation::create([
            'post_id' => $post->id,
            'user_id' => $donor->id,
            'gross_amount' => $grossAmount,
            'processing_fee' => $processingFee,
            'platform_fee' => $platformFee,
            'net_amount' => $netAmount,
            'currency' => $currency,
            'processor_payload' => $payload,
            'activity' => true,
        ]);

        $post->update([
            'paid' => (float) $post->paid + $netAmount,
        ]);

        $receiver = $post->user()->first();

        if ($receiver) {
            $notificationData = [
                'to' => 'receiver',
                'title' => 'New Donation Received',
                'image' => $this->userPhoto($donor->photo),
                'description' => $post->biography,
                'date' => Carbon::now(),
                'amount' => $netAmount,
                'metadata' => [
                    'gross_amount' => $grossAmount,
                    'processing_fee' => $processingFee,
                    'platform_fee' => $platformFee,
                    'currency' => $currency,
                    'order_id' => $orderId,
                ],
            ];

            Notification::send($receiver, new UserNotification($notificationData));

            $title = 'New Donation Received';
            $body = sprintf(
                'You received $%0.2f (after $%0.2f in fees).',
                $netAmount,
                $processingFee + $platformFee
            );

            $tokens = $receiver->device_token ? [$receiver->device_token] : [];

            if (!empty($tokens)) {
                Notification::send(null, new PushNotification($title, $body, 'ReceiverBalanceScreen', $tokens));
            }
        }

        return response()->json([
            'response' => true,
            'message' => ['Donation recorded successfully.'],
            'data' => [
                'donation_id' => $donation->id,
                'gross_amount' => $grossAmount,
                'processing_fee' => $processingFee,
                'platform_fee' => $platformFee,
                'net_amount' => $netAmount,
                'currency' => $currency,
            ],
        ]);
    }

    private function calculateSplit(float $gross): array
    {
        $processingFee = round($gross * 0.03, 2);
        $platformFee = round($gross * 0.02, 2);
        $netAmount = round($gross - $processingFee - $platformFee, 2);

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

    private function userPhoto(?string $photo): ?string
    {
        if ($photo === null) {
            return null;
        }

        return URL::to('/storage/' . ltrim($photo, '/'));
    }

    private function getAccessToken()
    {
        $response = Http::asForm()->withBasicAuth(
            env('PAYPAL_CLIENT_ID'),
            env('PAYPAL_SECRET')
        )->post(env('PAYPAL_API_BASE') . '/v1/oauth2/token', [
            'grant_type' => 'client_credentials'
        ]);

        if ($response->failed()) {
            abort(500, 'Failed to authenticate with PayPal');
        }

        return $response->json()['access_token'];
    }
}
