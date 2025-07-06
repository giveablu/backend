<?php

namespace App\Http\Controllers;

use App\Models\AppFaq;
use Illuminate\Http\Request;

class PaypalPayment extends Controller
{
    public function success()
    {
        return view('paypal-success');
    }

    public function cancel()
    {
        return view('paypal-cancel');
    }
}
