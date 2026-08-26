<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class PrmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly string $icon = 'mdi mdi-bell-outline',
        private readonly string $color = 'primary',
        private readonly ?string $url = null,
    ) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage(['title' => $this->title, 'message' => $this->message, 'icon' => $this->icon, 'color' => $this->color, 'url' => $this->url]);
    }
}