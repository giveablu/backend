<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Concerns\InteractsWithSocialProviders;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\SocialAuth\SocialAccountService;
use App\Services\SocialAuth\SocialAuthStateManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LinkedSocialAccountController extends Controller
{
    use InteractsWithSocialProviders;

    public function __construct(
        private SocialAuthStateManager $stateManager,
        private SocialAccountService $accountService
    ) {
    }

    public function redirect(Request $request, string $provider): JsonResponse
    {
        $user = $request->user();
        $enumProvider = $this->resolveProviderForRole($provider, $user->role);

        $validator = Validator::make($request->all(), [
            'redirect_uri' => ['required', 'url'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'message' => $validator->errors()->all(),
            ], 422);
        }

        $state = $this->stateManager->generateState(
            $enumProvider,
            'link',
            [
                'user_id' => $user->id,
                'role' => $user->role,
                'redirect_uri' => $request->input('redirect_uri'),
            ]
        );

        $driver = $this->buildDriver($enumProvider, $request->input('redirect_uri'), $state);

        if (method_exists($driver, 'forState')) {
            $driver->forState($state);
        }

        $redirectResponse = $driver->with(['state' => $state])->redirect();

        return response()->json([
            'response' => true,
            'message' => 'Authorization URL generated',
            'data' => [
                'authorization_url' => $redirectResponse->getTargetUrl(),
                'state' => $state,
            ],
        ]);
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        $user = $request->user();
        $enumProvider = $this->resolveProviderForRole($provider, $user->role);

        $rules = [
            'state' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
        ];

        if ($enumProvider === \App\Enums\SocialProvider::X) {
            $rules['oauth_token'] = ['required', 'string'];
            $rules['oauth_verifier'] = ['required', 'string'];
        } else {
            $rules['code'] = ['required', 'string'];
        }

        $validator = Validator::make($request->all(), $rules);

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

        if ((int) ($payload['user_id'] ?? 0) !== $user->id) {
            return response()->json([
                'response' => false,
                'message' => ['State token does not belong to this user.'],
            ], 400);
        }

        try {
            $driver = $this->buildDriver(
                $enumProvider,
                $request->input('redirect_uri'),
                $request->input('state')
            );

            if (method_exists($driver, 'forState')) {
                $driver->forState($request->input('state'));
            }

            if ($enumProvider === \App\Enums\SocialProvider::X) {
                $request->merge([
                    'oauth_token' => $request->input('oauth_token'),
                    'oauth_verifier' => $request->input('oauth_verifier'),
                ]);
            }

            $oauthUser = $driver->user();

            $result = $this->accountService->handleAuthentication(
                $enumProvider,
                $user->role,
                $oauthUser,
                linkUser: $user,
            );
        } catch (\Throwable $exception) {
            return response()->json([
                'response' => false,
                'message' => ['Unable to link social account.'],
            ], 400);
        } finally {
            $this->stateManager->forget($request->input('state'));
        }

        return (new UserResource($result['user']))->additional([
            'response' => true,
            'message' => 'Social account linked successfully',
            'warnings' => $result['warnings'],
        ]);
    }
}
