<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostNotification extends Notification
{
    use Queueable;

    public $type;
    public $message;
    public $url;

    /**
     * Create a new notification instance.
     */
    public function __construct($type, $message, $url = null)
    {
        $this->type = $type;
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
