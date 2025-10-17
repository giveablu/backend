<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithSocialProviders;
use App\Http\Resources\UserResource;
use App\Services\SocialAuth\SocialAccountService;
use App\Services\SocialAuth\SocialAuthStateManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SocialAuthController extends Controller
{
    use InteractsWithSocialProviders;

    public function __construct(
        private SocialAuthStateManager $stateManager,
        private SocialAccountService $accountService
    ) {
    }

    public function redirect(Request $request, string $role, string $provider): JsonResponse
    {
        $this->validateRole($role);
        $enumProvider = $this->resolveProviderForRole($provider, $role);

        $redirectUri = $request->input('redirect_uri');

        $driver = $this->buildDriver($enumProvider, $redirectUri);

        $state = $this->stateManager->generateState(
            $enumProvider,
            'login',
            [
                'role' => $role,
                'redirect_uri' => $redirectUri,
            ]
        );

        $redirectResponse = $driver->with(['state' => $state])->redirect();
        $url = $redirectResponse->getTargetUrl();

        return response()->json([
            'response' => true,
            'message' => 'Authorization URL generated',
            'data' => [
                'authorization_url' => $url,
                'state' => $state,
            ],
        ]);
    }

    public function callback(Request $request, string $role, string $provider): JsonResponse
    {
        $this->validateRole($role);
        $enumProvider = $this->resolveProviderForRole($provider, $role);

        $validator = Validator::make($request->all(), [
            'state' => ['required', 'string'],
            'code' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
            'device_token' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'message' => $validator->errors()->all(),
            ], 422);
        }

        $payload = $this->stateManager->resolve($request->input('state'));

        if (! $payload || ($payload['provider'] ?? null) !== $enumProvider->value) {
            return response()->json([
                'response' => false,
                'message' => ['Invalid or expired state token.'],
            ], 400);
        }

        if (($payload['role'] ?? null) !== $role) {
            return response()->json([
                'response' => false,
                'message' => ['Role mismatch for state token.'],
            ], 400);
        }

        try {
            $driver = $this->buildDriver($enumProvider, $request->input('redirect_uri'));
            $oauthUser = $driver->user();

            $result = $this->accountService->handleAuthentication(
                $enumProvider,
                $role,
                $oauthUser,
                linkUser: null,
                deviceToken: $request->input('device_token')
            );
        } catch (\Throwable $exception) {
            Log::error('Social login failed', [
                'provider' => $enumProvider->value,
                'role' => $role,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'response' => false,
                'message' => ['Unable to complete social authentication.'],
            ], 400);
        } finally {
            $this->stateManager->forget($request->input('state'));
        }

        $user = $result['user'];
        $token = $user->createToken('app-token')->plainTextToken;

        return (new UserResource($user))->additional([
            'response' => true,
            'message' => 'Logged-in successfully',
            'warnings' => $result['warnings'],
            'meta' => [
                'access_token' => $token,
            ],
        ]);
    }

    private function validateRole(string $role): void
    {
        if (! in_array($role, ['donor', 'receiver'], true)) {
            abort(404);
        }
    }
}
