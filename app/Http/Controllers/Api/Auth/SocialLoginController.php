<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Concerns\InteractsWithSocialProviders;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\SocialAuth\ArraySocialiteUser;
use App\Services\SocialAuth\SocialAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialLoginController extends Controller
{
    use InteractsWithSocialProviders;

    public function __construct(private SocialAccountService $accountService)
    {
    }

    public function SocialLogin(Request $request)
    {
        return $this->handleSocialLogin($request, 'receiver');
    }

    public function donorSocialLogin(Request $request)
    {
        return $this->handleSocialLogin($request, 'donor');
    }

    private function handleSocialLogin(Request $request, string $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'social_id' => ['required', 'string'],
            'service' => ['required', 'string'],
            'photo' => ['nullable', 'string'],
            'device_token' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'profile_url' => ['nullable', 'string'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'account_created_at' => ['nullable', 'date'],
            'location' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'raw_payload' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'message' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $provider = $this->resolveProviderForRole($request->input('service'), $role);
        } catch (\Throwable $exception) {
            return response()->json([
                'response' => false,
                'message' => ['Unsupported provider for this role.'],
            ], 422);
        }

        $raw = array_merge($request->input('raw_payload', []), array_filter([
            'followers_count' => $request->input('followers_count'),
            'location' => $request->input('location'),
            'description' => $request->input('bio'),
            'created_at' => $request->input('account_created_at'),
            'profile' => $request->input('profile_url'),
            'username' => $request->input('username'),
        ], fn ($value) => ! is_null($value)));

        $socialUser = new ArraySocialiteUser(
            id: $request->input('social_id'),
            email: $request->input('email'),
            name: $request->input('name'),
            nickname: $request->input('username'),
            avatar: $request->input('photo'),
            raw: $raw,
        );

        try {
            $result = $this->accountService->handleAuthentication(
                $provider,
                $role,
                $socialUser,
                deviceToken: $request->input('device_token')
            );
        } catch (\Throwable $exception) {
            return response()->json([
                'response' => false,
                'message' => ['Unable to process social login.'],
            ], 400);
        }

        $token = $result['user']->createToken('app-token')->plainTextToken;

        return (new UserResource($result['user']))->additional([
            'response' => true,
            'message' => 'Logged-in Successfully',
            'warnings' => $result['warnings'],
            'meta' => [
                'access_token' => $token,
            ],
        ]);
    }
}


