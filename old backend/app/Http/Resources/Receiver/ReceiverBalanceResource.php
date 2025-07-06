<?php

namespace App\Http\Resources\Receiver;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiverBalanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->user->name,
            'date' => Carbon::parse($this->created_at)->format('d-m-Y'),
            'amount' => $this->paid_amount,
            'photo' => $this->userPhoto($this->user)
        ];
    }

    private function userPhoto($user)
    {
        if (!is_null($user->photo)) {
            if (Str::contains($user->photo, 'http')) {
                return $user->photo;
            } else {
                return URL::to('/storage') . '/' . $user->photo;
            }
        } else {
            return null;
        }
    }
}

