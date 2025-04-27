<?php

namespace App\Http\Resources;

use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'to' => $this->data['to'],
            'title' => $this->data['title'],
            'image' => $this->data['image'],
            'description' => $this->data['description'],
            'amount' => $this->data['amount'],
            'date' => Carbon::parse($this->data['date'])->format('d/m/Y'),
        ];
    }
}
