<?php

namespace App\Http\Resources\Receiver;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\PostResource;
use App\Http\Resources\BankDetailResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiverProfileResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'search_id' => 'BLUC081245' . $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,

            'role' => $this->role,

            'joined_date' => Carbon::parse($this->updated_at)->format('m-d-Y'),

            'photo' => $this->userPhoto(),

            'bank_detail' => new BankDetailResource($this->bankDetail),

            'donation' => new PostResource($this->post),
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
