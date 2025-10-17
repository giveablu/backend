<?php

namespace App\Http\Resources;

use App\Enums\SocialVerificationStatus;
use App\Http\Resources\AppFaqResource;
use App\Http\Resources\BankDetailResource;
use App\Http\Resources\LinkedSocialAccountResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\SettingResource;
use App\Models\AppFaq;
use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $faqs = AppFaq::get();
        $setting = Setting::first();

    $status = $this->social_verification_status;

    return [
            'id' => $this->id,
            'search_id' => 'BLUC081245' . $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->when($request->segment(3) != 'profile', fn () => $this->phone),
            'role' => $this->role,
            'joined_date' => Carbon::parse($this->updated_at)->format('m-d-Y'),
            'gender' => $this->when($request->segment(2) == 'receiver-account' && $request->segment(3) == 'profile', fn () => optional($this->profile)->gender),
            'photo' => $this->userPhoto(),
            'bank_detail' => $this->when($request->segment(5) != 'detail', fn () => new BankDetailResource($this->bankDetail)),
            'donation' => new PostResource($this->post),
            'faqs' => $this->when($request->segment(2) == 'receiver-account' && $request->segment(3) == 'home', fn () => AppFaqResource::collection($faqs)),
            'default_amount' => $this->when($this->role == 'donor' && $setting, fn () => (int) $setting->default_amount),
            'setting' => $setting ? new SettingResource($setting) : null,
            'social_verification_status' => $status instanceof SocialVerificationStatus ? $status->value : ($status ?? 'pending'),
            'social_verified_at' => optional($this->social_verified_at)->toIsoString(),
            'social_accounts' => $this->when(
                $this->relationLoaded('socialAccounts'),
                fn () => LinkedSocialAccountResource::collection($this->socialAccounts)
            ),
        ];
    }

    private function userPhoto()
    {
        if (!is_null($this->photo)) {
            if (Str::contains($this->photo, 'http')) {
                return $this->photo;
            } else {
                return URL::to('/storage') . '/' . $this->photo;
            }
        } else {
            return null;
        }
    }
}
