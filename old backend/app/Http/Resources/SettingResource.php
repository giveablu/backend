<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'app_version' => $this->app_version,
            'app_feature' => explode("|", $this->app_feature),
        ];
    }
}
