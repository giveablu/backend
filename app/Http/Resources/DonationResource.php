<?php

namespace App\Http\Resources;

use App\Models\Post;
use App\Models\Donation;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
   
    public function toArray($request)
    {
        $post = Post::where('id', $this->post_id)->first();

        return [
            'activity' => $this->status(),
            'id' => $this->id,
            'donation_id' => $this->post_id,
            'receiver_name' => $post->user->name,
            'short_bio' => $this->when(!$request->segment(4), fn() => Str::limit($post->biography, 50, '...')),
            'biography' => $this->when($request->segment(4), fn() => $post->biography),
            'image' => URL::to('/storage') . '/' . $post->image,
            'paid_amount' => $this->paid_amount,
            'target_amount' => $this->post->amount,
            'date' => Carbon::parse($this->updated_at)->format('m-d-Y'),
            'tags' => $this->when($request->segment(4), fn() => TagResource::collection(($post->tags)))
        ];
    }

    protected function status(){
        if ($this->activity === 1) {
            return true;
        }
        else {
            return false;
        }
    }
}
