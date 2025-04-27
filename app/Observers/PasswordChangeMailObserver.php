<?php

namespace App\Observers;

use Illuminate\Support\Str;
use App\Mail\PasswordChangeMail;
use Illuminate\Support\Facades\Mail;

class PasswordChangeMailObserver
{
    public function updated($model)
    {
        if ($model->wasChanged('password')) {
            Mail::to($model->email)->send(new PasswordChangeMail);
        }
    }
}
