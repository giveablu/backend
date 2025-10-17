<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\SocialVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\LinkedSocialAccountResource;
use App\Models\SocialVerificationEvent;
use App\Models\User;
use App\Models\UserSocial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSocialController extends Controller
{
    public function index(User $user): JsonResponse
    {
        $user->load('socialAccounts');

        return response()->json([
            'response' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'role', 'social_verification_status', 'social_verified_at']),
                'linked' => LinkedSocialAccountResource::collection($user->socialAccounts),
                'events' => $user->socialVerificationEvents()->latest()->get()->map(function (SocialVerificationEvent $event) {
                    return [
                        'id' => $event->id,
                        'status' => $event->status->value,
                        'reason' => $event->reason,
                        'metadata' => $event->metadata,
                        'created_at' => $event->created_at->toIsoString(),
                    ];
                }),
            ],
        ]);
    }

    public function update(Request $request, User $user, UserSocial $social): JsonResponse
    {
        if ($social->user_id !== $user->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', 'in:' . implode(',', array_map(fn ($case) => $case->value, SocialVerificationStatus::cases()))],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'message' => $validator->errors()->all(),
            ], 422);
        }

        $status = SocialVerificationStatus::from($request->input('status'));

        $user->forceFill([
            'social_verification_status' => $status,
            'social_verified_at' => $status === SocialVerificationStatus::Verified ? now() : null,
            'social_verification_notes' => $request->input('notes'),
        ])->save();

        SocialVerificationEvent::create([
            'user_id' => $user->id,
            'user_social_id' => $social->id,
            'status' => $status,
            'reason' => $request->input('notes'),
            'metadata' => ['source' => 'admin'],
        ]);

        return response()->json([
            'response' => true,
            'message' => 'Verification status updated',
        ]);
    }
}
