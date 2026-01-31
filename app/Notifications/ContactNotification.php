<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ContactNotification extends Notification
{
    public function __construct(
        public string $type,
        public string $message,
        public string $url,
        public array $data = []
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Contact Form Submission')
            ->line($this->message)
            ->line('From: ' . $this->data['name'])
            ->line('Email: ' . $this->data['email'])
            ->line('Message: ' . substr($this->data['body'], 0, 100) . '...')
            ->action('View Message', $this->url);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'url' => $this->url,
            'data' => $this->data,
        ];
    }
}
