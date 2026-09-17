<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SystemAnnouncement extends Notification
{
    public function __construct(public string $message) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toDatabase(object $notifiable): array { return ['message' => $this->message]; }
}