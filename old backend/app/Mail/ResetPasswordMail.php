<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    private $token;
    private $email;
    private $userid;
    private $isOtp;

    public function __construct($token, $email, $userid, $isOtp = false)
    {
        $this->token = $token;
        $this->email = $email;
        $this->userid = $userid;
        $this->isOtp = $isOtp;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password - Better Lives United',
        );
    }

    public function content(): Content
    {
        if ($this->isOtp) {
            // Use the new OTP template
            return new Content(
                view: 'emails.reset-password-otp',
                with: [
                    'otp' => $this->token, // The token is actually the OTP
                    'email' => $this->email
                ]
            );
        } else {
            // Use the original URL-based template
            return new Content(
                view: 'emails.reset-password',
                with: [
                    'url' => route('reset', [$this->email, $this->userid, $this->token])
                ]
            );
        }
    }

    public function attachments(): array
    {
        return [];
    }
}