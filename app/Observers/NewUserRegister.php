<?php

namespace App\Observers;

use App\Mail\VerifyEmail;
use App\Models\MailOtp;
use Illuminate\Support\Facades\Mail;

class NewUserRegister
{
    public function created($model)
    {
        $data = [
            'new_code' => rand(1000, 9999),
            'new_mail' => $model->email,
            'expire_at' => now()->addMinutes(2)
        ];

        MailOtp::updateOrCreate([
            'user_id' => $model->id
        ], $data);

        Mail::to($model->email)->send(new VerifyEmail($data));
    }
}
