<?php

namespace App\Http\Resources\Receiver;

use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiverWithdrawResource extends JsonResource
{
    
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'amount' => $this->amount,
            'batch_id' => $this->batch_id,
            'batch_status' => $this->batch_status,
            'paypal_id' => $this->paypal_id,
            'status' => $this->status,
            'date' => Carbon::parse($this->created_at)->format('m-d-Y'),
        ];
    }
}
