<?php

namespace App\Http\Controllers\Api\Profile;

use App\Enums\SocialVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\LinkedSocialAccountResource;
use App\Models\UserSocial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load('socialAccounts');

        $status = $user->social_verification_status;

        return response()->json([
            'response' => true,
            'data' => [
                'verification_status' => $status instanceof SocialVerificationStatus ? $status->value : ($status ?? 'pending'),
                'social_verified_at' => optional($user->social_verified_at)->toIsoString(),
                'linked' => LinkedSocialAccountResource::collection($user->socialAccounts()->orderByDesc('is_primary')->get()),
            ],
        ]);
    }

    public function destroy(Request $request, UserSocial $social): JsonResponse
    {
        $user = $request->user();

        if ($social->user_id !== $user->id) {
            abort(404);
        }

        $linkedCount = $user->socialAccounts()->count();
        if ($linkedCount <= 1 && $user->role === 'receiver') {
            return response()->json([
                'response' => false,
                'message' => ['Recipients must keep at least one social account linked.'],
            ], 422);
        }

        DB::transaction(function () use ($user, $social) {
            $wasPrimary = $social->is_primary;
            $social->delete();

            if ($wasPrimary) {
                $next = $user->socialAccounts()->latest('last_synced_at')->first();
                if ($next) {
                    $next->is_primary = true;
                    $next->save();
                }
            }
        });

        return response()->json([
            'response' => true,
            'message' => 'Social account unlinked',
        ]);
    }
}
