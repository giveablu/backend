<?php

namespace App\Http\Resources;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // print_r(request()->segment(3), request()->segment(4));exit;
        return [
            'id' => $this->id,
            'receiver_name' => $this->receiver_name,
            'amount' => $this->amount,
            'biography' => $this->biography,
            'date' => Carbon::parse($this->updated_at)->format('m-d-Y'),
            'date_time' => Carbon::parse($this->updated_at)->format('m-d-Y h:i a'),
            'image' => URL::to('/storage') . '/' . $this->image,
            'location' => $this->location,
            'age' => $this->age,
            'family_size' => $this->family_size,
            'tags' => TagResource::collection($this->tags),
            'activity' => $this->activity,
            'donation_id' => $this->donation_id,
            'verification_status' => $this->verification_status,
            'story' => $this->story,
            'needs' => $this->needs,
            'impact_description' => $this->impact_description,
            'short_description' => $this->when(!request()->segment(4) && $request->segment(2) != 'receiver-account', fn() => $this->short_desc),
            'paid' => $this->when($request->segment(2) != 'donor-account', fn() => $this->paid),
        ];
    }
}
