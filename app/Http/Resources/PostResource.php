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

            'amount' => $this->amount,

            'short_description' => $this->when(!request()->segment(4) && $request->segment(2) != 'receiver-account', fn() => $this->short_desc),

            'image' => URL::to('/storage') . '/' . $this->image,

            'biography' => $this->biography,

            'date' => $this->when(
                request()->segment(3) == 'donations' && !$request->segment(4),
                fn() => Carbon::parse($this->updated_at)->format('m-d-Y'),
                Carbon::parse($this->updated_at)->format('m-d-Y') // Fallback to updated_at format
            ),

            'date-time' => $this->when(
                request()->segment(3) == 'donations' && $request->segment(4),
                fn() => Carbon::parse($this->updated_at)->format('m-d-Y h:i a'),
                Carbon::parse($this->updated_at)->format('m-d-Y h:i a') // Fallback to updated_at format
            ),

            'paid' => $this->when($request->segment(2) != 'donor-account', fn() => $this->paid),

            'tags' => $this->when($request->segment(3) != 'home', fn() => TagResource::collection(($this->tags))),
        ];
    }
}
