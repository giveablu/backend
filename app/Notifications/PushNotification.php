<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Illuminate\Notifications\Messages\MailMessage;

class PushNotification extends Notification
{
    use Queueable;

    public $title;
    public $body;
    public $tokens;
    public $pageName;

    public function __construct($title, $body, $pageName, $tokens)
    {
        $this->title = $title;
        $this->body = $body;
        $this->tokens = $tokens;
        $this->pageName = $pageName;
    }


    public function via($notifiable)
    {
        return ['firebase'];
    }


    public function toFirebase($notifiable)
    {
        return (new FirebaseMessage)
            ->withTitle($this->title)
            ->withBody($this->body)
            ->withAdditionalData([
                'pageName' => $this->pageName,
            ])
            ->asNotification($this->tokens);
    }
}
