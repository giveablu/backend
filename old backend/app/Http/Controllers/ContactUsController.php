<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Mail\ContactUsMail;
use App\Mail\AdminContactUsMail;

use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    
    public function contactUs()
    {
        return view('pages.contactUs');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_no' => 'required|max:12'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $contactData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone_no'),
            'message' => $request->input('message')
        ];
    
        ContactUs::create($contactData);
        Mail::to($contactData['email'])->send(new ContactUsMail($contactData['name']));

        $companyMail = 'giveablu@gmail.com';
        Mail::to($companyMail)->send(new AdminContactUsMail($contactData));


        return redirect()->back()->with('success', 'Contact form submitted successfully!');
    }
}
