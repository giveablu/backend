<?php

namespace App\Http\Resources\Donor;

use App\Models\Post;
use App\Models\DeletePost;
use App\Http\Resources\TagResource;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class DonorHomeResource extends JsonResource
{
    function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function toArray($request)
    {
        $deleted = DeletePost::where('post_id', $this->id)->where('user_id', $request->user()->id)->first();

        return [
            'activity' => $this->status($deleted),
            'receiver_name' => $this->user->name,
            'id' => $this->id,
            'amount' => $this->amount,
            'biography' => $this->biography,
            'date_time' => $this->updated_at,
            'image' => URL::to("/storage/{$this->image}"),
            'tags' => TagResource::collection(($this->tags))
        ];
    }

    protected function status($deleted)
    {
        if ($deleted) {
            return false;
        } else {
            return true;
        }
    }
}
