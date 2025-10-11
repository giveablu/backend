<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    use Queueable;

    public function __construct(private readonly User $user)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject('New user joined Blu')
            ->greeting('Hello ' . ($notifiable->name ?? 'Admin'))
            ->line('A new user has just registered on Blu:')
            ->line('Name: ' . $this->user->name)
            ->line('Role: ' . ucfirst($this->user->role))
            ->line('Email: ' . ($this->user->email ?? '—'))
            ->line('Phone: ' . ($this->user->phone ?? '—'))
            ->line('Registered: ' . $this->user->created_at?->timezone(config('app.timezone', 'UTC'))?->format('M d, Y H:i'))
            ->action('Open admin dashboard', url('/admin/users'))
            ->line('You can review, verify, or update their account from the admin panel.');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'role' => $this->user->role,
            'registered_at' => $this->user->created_at?->toIso8601String(),
        ];
    }
}
