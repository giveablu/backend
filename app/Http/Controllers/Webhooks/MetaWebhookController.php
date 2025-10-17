<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    /**
     * Handle the GET verification handshake from Meta (Facebook/Instagram).
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $verifyToken = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = Config::get('services.instagram.verify_token');

        if ($mode === 'subscribe' && $verifyToken === $expectedToken && filled($challenge)) {
            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Meta webhook verification failed', [
            'mode' => $mode,
            'provided_token' => $verifyToken,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle webhook payloads from Meta (Facebook/Instagram).
     */
    public function handle(Request $request): JsonResponse
    {
        Log::info('Meta webhook payload received', [
            'payload' => $request->all(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}
