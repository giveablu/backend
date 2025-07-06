<?php

namespace App\Http\Resources\Receiver;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\PostResource;
use App\Http\Resources\BankDetailResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiverProfileResource extends JsonResource
{

    public function toArray($request)
    {
        $user = $this;
        $bank = $this->bankDetail;
        $post = $this->post;
        return [
            // User fields
            'id' => $user->id,
            'search_id' => $user->search_id ?? ('BLUC081245' . $user->id),
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'gender' => $user->gender,
            'joined_date' => $user->joined_date ? $user->joined_date->format('m-d-Y') : ($user->updated_at ? $user->updated_at->format('m-d-Y') : null),
            'photo' => $this->userPhoto(),

            // Bank/PayPal fields
            'bank_detail' => $bank ? [
                'id' => $bank->id,
                'account_name' => $bank->account_name,
                'account_id_type' => $bank->account_id_type,
                'paypal_account_id' => $bank->paypal_account_id,
                'currency' => $bank->currency
            ] : null,

            // Post/Donation fields
            'post_id' => $post ? $post->id : null,
            'amount' => $post ? $post->amount : null,
            'biography' => $post ? $post->biography : null,
            'image' => $post && $post->image ? \Illuminate\Support\Facades\URL::to('/storage') . '/' . $post->image : null,
            'paid' => $post ? $post->paid : null,
            'activity' => $post ? $post->activity : null,
            'donation_id' => $post ? $post->donation_id : null,
            'verification_status' => $post ? $post->verification_status : null,
            'story' => $post ? $post->story : null,
            'needs' => $post ? $post->needs : [],
            'impact_description' => $post ? $post->impact_description : null,
            'family_size' => $post ? $post->family_size : null,
            'location' => $post ? $post->location : null,
            'age' => $post ? $post->age : null,
            // Tags/Hardships
            'tags' => $post && $post->tags ? \App\Http\Resources\TagResource::collection($post->tags) : [],
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
