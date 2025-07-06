<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankDetailResource extends JsonResource
{
    
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // 'bank_name' => $this->bank_name,
            'account_name' => $this->account_name,
            'account_id_type' => $this->account_id_type,
            'paypal_account_id' => $this->paypal_account_id,
            'currency' => $this->currency
        ];
    }
}
