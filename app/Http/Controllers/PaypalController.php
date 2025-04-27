<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Donation;

class PaypalController extends Controller
{
    public function captureOrder(Request $request)
    {
        $orderId = $request->input('orderID');

        if (!$orderId) {
            return response()->json(['error' => 'Missing order ID.'], 400);
        }

        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post(env('PAYPAL_API_BASE') . "/v2/checkout/orders/{$orderId}/capture");

        if ($response->failed()) {
            Log::error('PAYPAL CAPTURE FAILED', $response->json());
            return response()->json([
                'error' => 'Failed to capture order.',
                'details' => $response->json()
            ], 500);
        }

        $data = $response->json();
        Log::info('PAYPAL CAPTURE RESPONSE', $data);

        // Save to database
        Donation::create([
            'order_id' => $data['id'],
            'amount' => $data['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
            'currency' => $data['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
            'payer_email' => $data['payer']['email_address'] ?? 'unknown',
            'status' => $data['status'],
            'paypal_data' => json_encode($data),
        ]);

        return view('paypal.thankyou', [
            'transaction' => $data
        ]);
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
