<?php

namespace App\Http\Resources\Donor;

use App\Http\Resources\TagResource;
use App\Models\DonorPreference;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class DonorProfileResource extends JsonResource
{
    function __construct(User $model)
    {
        parent::__construct($model);
    }
    
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'joined_date' => Carbon::parse($this->updated_at)->format('m-d-Y'),
            'photo' => $this->photo(),
            'preferences' => $this->buildPreferences($this->donorPreference)
        ];
    }

    protected function photo()
    {
        if (is_null($this->photo)) {
            return null;
        } else {
            return URL::to('/storage') . '/' . $this->photo;
        }
    }

    protected function buildPreferences(?DonorPreference $preference): ?array
    {
        if (! $preference) {
            return null;
        }

        $hardshipIds = $preference->preferred_hardship_ids ?? [];
        $hardshipTags = empty($hardshipIds) ? collect() : Tag::whereIn('id', $hardshipIds)->get();

        return [
            'country' => $preference->preferred_country,
            'region' => $preference->preferred_region,
            'city' => $preference->preferred_city,
            'hardship_ids' => $hardshipIds,
            'hardships' => TagResource::collection($hardshipTags),
        ];
    }
}
