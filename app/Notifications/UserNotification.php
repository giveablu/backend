<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'to' => $this->data['to'],
            'title' => $this->data['title'],
            'image' => $this->data['image'],
            'description' => $this->data['description'],
            'amount' => $this->data['amount'],
            'date' => $this->data['date'],
            'metadata' => $this->data['metadata'] ?? null,
        ];
    }
}
