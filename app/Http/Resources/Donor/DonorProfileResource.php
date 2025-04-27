<?php

namespace App\Http\Resources\Donor;

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
            'photo' => $this->photo()
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
}
