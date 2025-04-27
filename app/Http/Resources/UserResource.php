<?php

namespace App\Http\Resources;

use App\Models\AppFaq;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\PostResource;
use App\Http\Resources\AppFaqResource;
use App\Http\Resources\BankDetailResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $faqs = AppFaq::get();
        $setting = Setting::first();

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
